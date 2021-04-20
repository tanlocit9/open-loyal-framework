<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\DataFixtures\ORM;

use Broadway\CommandHandling\CommandBus;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use OpenLoyalty\Component\Transaction\Domain\Command\RegisterTransaction;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;

/**
 * Class LoadNotMatchedTransactionData.
 */
class LoadNotMatchedTransactionData extends ContainerAwareFixture implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $phoneNumberFaker = Factory::create();
        /** @var UuidGeneratorInterface $uuidGenerator */
        $uuidGenerator = $this->container->get('broadway.uuid.generator');
        /** @var CommandBus $bus */
        $bus = $this->container->get('broadway.command_handling.command_bus');

        $transactionData = [
            'documentNumber' => '123',
            'purchasePlace' => 'wroclaw',
            'purchaseDate' => (new \DateTime('+1 day'))->getTimestamp(),
            'documentType' => 'sell',
        ];
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
                    [
                        'key' => 'test',
                        'value' => 'label2',
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
        $customerData = [
            'name' => 'Jan Nowak',
            'email' => 'not_matched@example.com',
            'nip' => 'aaa',
            'phone' => $phoneNumberFaker->e164PhoneNumber,
            'loyaltyCardNumber' => 'not_matched_card_number',
            'address' => [
                'street' => 'Kościuszki',
                'address1' => '12',
                'city' => 'Warsaw',
                'country' => 'PL',
                'province' => 'Mazowieckie',
                'postal' => '00-800',
            ],
        ];

        // sell transactions
        foreach ($this->getTransactionsDocumentNumber() as $documentNumber) {
            // sell transaction
            $transactionData['documentNumber'] = $documentNumber;
            $bus->dispatch(
                new RegisterTransaction(
                    new TransactionId($uuidGenerator->generate()),
                    $transactionData,
                    $customerData,
                    $items
                )
            );
        }

        // return transactions
        foreach ($this->getTransactionsDocumentNumber() as $documentNumber) {
            // sell transaction
            $transactionData['documentNumber'] = 'return.'.$documentNumber;
            $transactionData['documentType'] = 'return';
            $bus->dispatch(
                new RegisterTransaction(
                    new TransactionId($uuidGenerator->generate()),
                    $transactionData,
                    $customerData,
                    $items,
                    null,
                    null,
                    null,
                    null,
                    $documentNumber
                )
            );
        }
    }

    /**
     * @return array
     */
    public function getTransactionsDocumentNumber(): array
    {
        return [
            'not-matched',
            'not_matched',
            'notMatched',
            'NotMatched123',
            'not\matched',
            'NOT\MATCHED\123',
            'not.matched',
            'not+matched',
        ];
    }
}
