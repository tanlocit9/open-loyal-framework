<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Transaction\Tests\Unit\ReadModel;

use OpenLoyalty\Component\Transaction\Domain\Transaction;
use OpenLoyalty\Component\Transaction\Domain\TransactionRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Broadway\ReadModel\InMemory\InMemoryRepository;
use Broadway\ReadModel\Projector;
use Broadway\ReadModel\Testing\ProjectorScenarioTestCase;
use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use OpenLoyalty\Component\Transaction\Domain\CustomerId;
use OpenLoyalty\Component\Transaction\Domain\Event\CustomerWasAssignedToTransaction;
use OpenLoyalty\Component\Transaction\Domain\Event\LabelsWereAppendedToTransaction;
use OpenLoyalty\Component\Transaction\Domain\Event\LabelsWereUpdated;
use OpenLoyalty\Component\Transaction\Domain\Event\TransactionWasRegistered;
use OpenLoyalty\Component\Transaction\Domain\PosId;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsProjector;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class TransactionDetailsProjectorTest.
 */
class TransactionDetailsProjectorTest extends ProjectorScenarioTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createProjector(InMemoryRepository $repository): Projector
    {
        /** @var PosRepository|MockObject $posRepo */
        $posRepo = $this->getMockBuilder(PosRepository::class)->getMock();
        $posRepo->method('byId')->willReturn(null);

        $transaction = $this->getMockBuilder(Transaction::class)->getMock();

        /** @var TransactionRepository|MockObject $transactionRepository */
        $transactionRepository = $this->getMockBuilder(TransactionRepository::class)->disableOriginalConstructor()->getMock();
        $transactionRepository->method('load')->willReturn($transaction);

        return new TransactionDetailsProjector($repository, $posRepo, $transactionRepository);
    }

    /**
     * @test
     */
    public function it_created_read_model_when_new_transaction_registered(): void
    {
        $transactionId = new TransactionId('00000000-0000-0000-0000-000000000000');
        $posId = new PosId('00000000-0000-0000-0000-000000000011');

        $transactionData = $this->getTransactionData();
        $items = [
            [
                'sku' => ['code' => 'SKU1'],
                'name' => 'item 1',
                'quantity' => 1,
                'grossValue' => 1,
                'category' => 'aaa',
                'maker' => 'sss',
                'labels' => [
                    [
                        'key' => 'test',
                        'value' => 'label',
                    ],
                ],
            ],
            [
                'sku' => ['code' => 'SKU2'],
                'name' => 'item 2',
                'quantity' => 2,
                'grossValue' => 2,
                'category' => 'bbb',
                'maker' => 'ccc',
            ],
        ];

        $customerData = $this->getCustomerData();

        $expectedReadModel = TransactionDetails::deserialize(
            array_merge($transactionData, [
                'transactionId' => (string) $transactionId,
                'customerData' => $customerData,
                'items' => $items,
            ])
        );
        $expectedReadModel->setPosId($posId);
        $expectedReadModel->setLabels([
            new Label('test_label', 'some value'),
        ]);

        $this->scenario->given([])
            ->when(new TransactionWasRegistered(
                $transactionId,
                $transactionData,
                $customerData,
                $items,
                $posId,
                null,
                null,
                null,
                null,
                [
                    new Label('test_label', 'some value'),
                ]
            ))
            ->then([
                $expectedReadModel,
            ]);
    }

    /**
     * @test
     */
    public function it_updates_read_model_when_customer_was_assigned_to_transaction(): void
    {
        $transactionId = new TransactionId('00000000-0000-0000-0000-000000000000');
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000011');

        $expectedReadModel = TransactionDetails::deserialize(
            array_merge([
                'transactionId' => (string) $transactionId,
                'customerData' => $this->getCustomerData(),
            ], $this->getTransactionData())
        );

        $expectedReadModel->setCustomerId($customerId);
        $expectedReadModel->getCustomerData()->updateEmailAndPhone('test@example.com', '123');

        $this->scenario
            ->given([
                new TransactionWasRegistered($transactionId, $this->getTransactionData(), $this->getCustomerData()),
            ])
            ->when(new CustomerWasAssignedToTransaction($transactionId, $customerId, 'test@example.com', '123'))
            ->then(array(
                $expectedReadModel,
            ));
    }

    /**
     * @test
     */
    public function it_updates_read_model_when_labels_are_appended_to_transaction(): void
    {
        $transactionId = new TransactionId('00000000-0000-0000-0000-000000000000');

        $expectedReadModel = TransactionDetails::deserialize(
            array_merge([
                'transactionId' => (string) $transactionId,
                'customerData' => $this->getCustomerData(),
            ], $this->getTransactionData())
        );
        $expectedReadModel->setLabels([
            new Label('test_label', 'some value'),
            new Label('added label', 'test'),
        ]);
        $this->scenario
            ->given([
                new TransactionWasRegistered(
                    $transactionId,
                    $this->getTransactionData(),
                    $this->getCustomerData(),
                    [],
                    null,
                    null,
                    null,
                    null,
                    null,
                    [
                        new Label('test_label', 'some value'),
                    ]
                ),
            ])
            ->when(new LabelsWereAppendedToTransaction($transactionId, [['key' => 'added label', 'value' => 'test']]))
            ->then(array(
                $expectedReadModel,
            ));
    }

    /**
     * @test
     */
    public function it_updates_read_model_when_labels_are_updated(): void
    {
        $transactionId = new TransactionId('00000000-0000-0000-0000-000000000000');

        $expectedReadModel = TransactionDetails::deserialize(
            array_merge([
                'transactionId' => (string) $transactionId,
                'customerData' => $this->getCustomerData(),
            ], $this->getTransactionData())
        );
        $expectedReadModel->setLabels([
            new Label('edited label', 'test'),
        ]);
        $this->scenario
            ->given([
                new TransactionWasRegistered(
                    $transactionId,
                    $this->getTransactionData(),
                    $this->getCustomerData(),
                    [],
                    null,
                    null,
                    null,
                    null,
                    null,
                    [
                        new Label('test_label', 'some value'),
                    ]
                ),
            ])
            ->when(new LabelsWereUpdated($transactionId, [['key' => 'edited label', 'value' => 'test']]))
            ->then(array(
                $expectedReadModel,
            ));
    }

    /**
     * @return array
     */
    protected function getTransactionData(): array
    {
        return [
            'documentNumber' => '123',
            'purchasePlace' => 'wroclaw',
            'purchaseDate' => '1471859115',
            'documentType' => 'sell',
            'grossValue' => 0.0,
        ];
    }

    /**
     * @return array
     */
    protected function getCustomerData(): array
    {
        return [
            'name' => 'Jan Nowak',
            'email' => 'ol@oy.com',
            'nip' => 'aaa',
            'phone' => '123',
            'loyaltyCardNumber' => '222',
            'address' => [
                'street' => 'Bagno',
                'address1' => '12',
                'city' => 'Warszawa',
                'country' => 'PL',
                'province' => 'Mazowieckie',
                'postal' => '00-800',
            ],
        ];
    }
}
