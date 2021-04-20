<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Account\Tests\Unit\Infrastructure\Event\Listener;

use Broadway\CommandHandling\CommandBus;
use Broadway\ReadModel\Repository;
use Broadway\Repository\Repository as AggregateRootRepository;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Command\SpendPoints;
use OpenLoyalty\Component\Account\Domain\Model\PointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\SpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetails;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetailsRepository;
use OpenLoyalty\Component\Account\Domain\TransactionId as AccountTransactionId;
use OpenLoyalty\Component\Account\Infrastructure\Event\Listener\RevokePointsOnReturnTransactionListener;
use OpenLoyalty\Component\Transaction\Domain\CustomerId;
use OpenLoyalty\Component\Transaction\Domain\Event\CustomerWasAssignedToTransaction;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;
use OpenLoyalty\Component\Account\Domain\CustomerId as AccountCustomerId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class RevokePointsOnReturnTransactionListenerTest.
 */
final class RevokePointsOnReturnTransactionListenerTest extends TestCase
{
    /**
     * @var string
     */
    protected $uuid = '00000000-0000-0000-0000-000000000000';

    /**
     * @var string
     */
    protected $newUuid = '00000000-0000-0000-0000-000000000001';

    /**
     * @var string
     */
    protected $documentNumber = '123';

    /**
     * @var TransactionDetails|MockObject
     */
    protected $transaction;

    /**
     * @var TransactionDetails|MockObject
     */
    protected $revisedTransaction;

    /**
     * @var Transaction|MockObject
     */
    protected $transactionAggregateRoot;

    /**
     * @var Transaction|MockObject
     */
    protected $revisedTransactionAggregateRoot;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->transaction = $this->getMockBuilder(TransactionDetails::class)->disableOriginalConstructor()->getMock();
        $this->revisedTransaction = $this->getMockBuilder(TransactionDetails::class)->disableOriginalConstructor()->getMock();

        $this->transaction->method('getTransactionId')->willReturn(new TransactionId($this->newUuid));
        $this->revisedTransaction->method('getTransactionId')->willReturn(new TransactionId($this->uuid));

        $this->transaction->method('getId')->willReturn($this->newUuid);
        $this->transaction->method('getGrossValue')->willReturn(100);
        $this->transaction->method('getRevisedDocument')->willReturn($this->documentNumber);
        $this->transaction->method('getDocumentType')->willReturn(Transaction::TYPE_RETURN);

        $this->revisedTransaction->method('getGrossValue')->willReturn(200);
        $this->revisedTransaction->method('getId')->willReturn($this->uuid);

        $this->transactionAggregateRoot = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $this->revisedTransactionAggregateRoot = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();

        $this->transactionAggregateRoot->method('getTransactionId')->willReturn(new TransactionId($this->newUuid));
        $this->revisedTransactionAggregateRoot->method('getTransactionId')->willReturn(new TransactionId($this->uuid));

        $this->transactionAggregateRoot->method('getId')->willReturn($this->newUuid);
        $this->transactionAggregateRoot->method('getGrossValue')->willReturn(100);
        $this->transactionAggregateRoot->method('getRevisedDocument')->willReturn($this->documentNumber);
        $this->transactionAggregateRoot->method('getDocumentType')->willReturn(Transaction::TYPE_RETURN);

        $this->revisedTransactionAggregateRoot->method('getGrossValue')->willReturn(200);
        $this->revisedTransactionAggregateRoot->method('getId')->willReturn($this->uuid);
    }

    /**
     * @test
     */
    public function it_revokes_points_for_return_transaction(): void
    {
        $listener = new RevokePointsOnReturnTransactionListener(
            $this->getTransactionRepository(),
            $this->getTransactionDetailsRepository(),
            $this->getPointsTransferRepository(1000, 0),
            $this->getAccountDetailsRepository(),
            $this->getCommandBus(
                new SpendPoints(
                    new AccountId($this->uuid),
                    new SpendPointsTransfer(
                        new PointsTransferId($this->uuid),
                        500.0,
                        null,
                        false,
                        null,
                        PointsTransfer::ISSUER_SYSTEM,
                        new AccountTransactionId($this->newUuid),
                        new AccountTransactionId($this->uuid)
                    )
                ),
                1
            ),
            $this->getUuidGenerator()
        );

        $listener->onCustomerWasAssignedToTransaction(new CustomerWasAssignedToTransaction(
            new TransactionId($this->newUuid),
            new CustomerId($this->uuid)
        ));
    }

    /**
     * @test
     */
    public function it_not_revokes_points_for_return_transaction_if_already_revoked(): void
    {
        $listener = new RevokePointsOnReturnTransactionListener(
            $this->getTransactionRepository(),
            $this->getTransactionDetailsRepository(),
            $this->getPointsTransferRepository(1000, 1000),
            $this->getAccountDetailsRepository(),
            $this->getCommandBus(
                new SpendPoints(
                    new AccountId($this->uuid),
                    new SpendPointsTransfer(
                        new PointsTransferId($this->uuid),
                        500.0,
                        null,
                        false,
                        null,
                        PointsTransfer::ISSUER_SYSTEM,
                        new AccountTransactionId($this->newUuid),
                        new AccountTransactionId($this->uuid)
                    )
                ),
                0
            ),
            $this->getUuidGenerator()
        );

        $listener->onCustomerWasAssignedToTransaction(new CustomerWasAssignedToTransaction(
            new TransactionId($this->newUuid),
            new CustomerId($this->uuid)
        ));
    }

    /**
     * @return MockObject|UuidGeneratorInterface
     */
    protected function getUuidGenerator(): MockObject
    {
        $mock = $this->getMockBuilder(UuidGeneratorInterface::class)->getMock();
        $mock->method('generate')->willReturn($this->uuid);

        return $mock;
    }

    /**
     * @return MockObject|AggregateRootRepository
     */
    protected function getTransactionRepository(): MockObject
    {
        $repository = $this->getMockBuilder(AggregateRootRepository::class)->getMock();
        $repository->method('load')->with($this->isType('string'))->willReturnCallback(function (string $id) {
            switch ($id) {
                case $this->uuid:
                    return $this->revisedTransactionAggregateRoot;
                case $this->newUuid:
                    return $this->transactionAggregateRoot;
            }

            return null;
        });

        return $repository;
    }

    /**
     * @return MockObject|TransactionDetailsRepository
     */
    protected function getTransactionDetailsRepository(): MockObject
    {
        $repo = $this->getMockBuilder(TransactionDetailsRepository::class)->getMock();
        $repo->method('findBy')->with($this->arrayHasKey('documentNumberRaw'))->willReturnCallback(function ($params) {
            switch ($params['documentNumberRaw']) {
                case $this->documentNumber:
                    return [$this->revisedTransaction];
            }

            return null;
        });

        return $repo;
    }

    /**
     * @param      $all
     * @param null $alreadyRevoked
     *
     * @return MockObject|PointsTransferDetailsRepository
     */
    protected function getPointsTransferRepository($all, $alreadyRevoked = null): MockObject
    {
        $repo = $this->getMockBuilder(PointsTransferDetailsRepository::class)->getMock();
        $repo->method('findBy')->with($this->arrayHasKey('type'))->will($this->returnCallback(function ($params) use ($all, $alreadyRevoked) {
            switch ($params['type']) {
                case PointsTransferDetails::TYPE_ADDING:
                    $transfer = new PointsTransferDetails(
                        new PointsTransferId($this->uuid),
                        new AccountCustomerId($this->uuid),
                        new AccountId($this->uuid)
                    );
                    $transfer->setValue($all);

                    return [$transfer];
                case PointsTransferDetails::TYPE_SPENDING:
                    if (!$alreadyRevoked) {
                        return [];
                    }
                    $transfer = new PointsTransferDetails(
                        new PointsTransferId($this->uuid),
                        new AccountCustomerId($this->uuid),
                        new AccountId($this->uuid)
                    );
                    $transfer->setValue($alreadyRevoked);

                    return [$transfer];
            }

            return [];
        }));

        return $repo;
    }

    /**
     * @return MockObject|Repository
     */
    protected function getAccountDetailsRepository(): MockObject
    {
        $account = $this->getMockBuilder(AccountDetails::class)->disableOriginalConstructor()->getMock();
        $account->method('getAccountId')->willReturn(new AccountId($this->uuid));

        $repo = $this->getMockBuilder(Repository::class)->getMock();
        $repo->method('findBy')->with($this->arrayHasKey('customerId'))->willReturn([$account]);

        return $repo;
    }

    /**
     * @param $expected
     * @param $times
     *
     * @return MockObject|CommandBus
     */
    protected function getCommandBus($expected, $times): MockObject
    {
        $mock = $this->getMockBuilder(CommandBus::class)->getMock();
        $mock->expects($this->exactly($times))->method('dispatch')->with($this->equalTo($expected, 2));

        return $mock;
    }
}
