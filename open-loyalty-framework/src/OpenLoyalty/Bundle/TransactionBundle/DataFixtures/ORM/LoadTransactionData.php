<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\DataFixtures\ORM;

use Broadway\CommandHandling\CommandBus;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use OpenLoyalty\Bundle\PosBundle\DataFixtures\ORM\LoadPosData;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;
use OpenLoyalty\Component\Transaction\Domain\Command\RegisterTransaction;
use OpenLoyalty\Component\Transaction\Domain\PosId;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;

/**
 * Class LoadTransactionData.
 */
class LoadTransactionData extends ContainerAwareFixture implements FixtureInterface, OrderedFixtureInterface
{
    const TRANSACTION_ID = '00000000-0000-1111-0000-000000000000';
    const TRANSACTION_COUPONS_ID = '00000000-0000-1111-0000-000000002121';
    const TRANSACTION_COUPONS_USED_ID = '00000000-0000-1111-0000-000000002123';
    const TRANSACTION2_ID = '00000000-0000-1111-0000-000000000002';
    const TRANSACTION3_ID = '00000000-0000-1111-0000-000000000003';
    const TRANSACTION4_ID = '00000000-0000-1111-0000-000000000004';
    const TRANSACTION5_ID = '00000000-0000-1111-0000-000000000005';
    const TRANSACTION6_ID = '00000000-0000-1111-0000-000000000006';
    const TRANSACTION7_ID = '00000000-0000-1111-0000-000000000007';
    const TRANSACTION8_ID = '00000000-0000-1111-0000-000000000008';
    const TRANSACTION9_ID = '00000000-0000-1111-0000-000000000009';

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $phoneNumber = $faker->e164PhoneNumber;

        $transactionData = [
            'documentNumber' => '123',
            'purchasePlace' => 'New York',
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

        /** @var CommandBus $bus */
        $bus = $this->container->get('broadway.command_handling.command_bus');
        $customerData = [
            'name' => 'John Doe',
            'email' => 'ol@oy.com',
            'nip' => 'aaa',
            'phone' => $phoneNumber,
            'loyaltyCardNumber' => '222',
            'address' => [
                'street' => 'Oxford Street',
                'address1' => '12',
                'city' => 'New York',
                'country' => 'US',
                'province' => 'New York',
                'postal' => '10001',
            ],
        ];

        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION_ID),
                $transactionData,
                $customerData,
                $items,
                new PosId(LoadPosData::POS_ID),
                null,
                null,
                null,
                null,
                [
                    ['key' => 'scan_id', 'value' => 'abc123789def-abc123789def-abc123789def-abc123789def'],
                ]
            )
        );

        $transactionData['documentNumber'] = '345';

        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION2_ID),
                $transactionData,
                [
                    'name' => 'John Doe',
                    'email' => 'open@oloy.com',
                    'nip' => 'aaa',
                    'phone' => $phoneNumber,
                    'loyaltyCardNumber' => 'sa2222',
                    'address' => [
                        'street' => 'Oxford Street',
                        'address1' => '12',
                        'city' => 'New York',
                        'country' => 'US',
                        'province' => 'New York',
                        'postal' => '10001',
                    ],
                ],
                $items,
                null,
                null,
                null,
                null,
                null,
                [
                    ['key' => 'scan_id', 'value' => '456'],
                ]
            )
        );

        $transactionData['documentNumber'] = '888';
        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION5_ID),
                $transactionData,
                [
                    'name' => 'John Doe',
                    'email' => 'o@lo.com',
                    'nip' => 'aaa',
                    'phone' => $phoneNumber,
                    'loyaltyCardNumber' => 'sa21as222',
                    'address' => [
                        'street' => 'Oxford Street',
                        'address1' => '12',
                        'city' => 'New York',
                        'country' => 'US',
                        'province' => 'New York',
                        'postal' => '10001',
                    ],
                ],
                $items,
                null,
                null,
                null,
                null,
                null,
                [
                    ['key' => 'scan_id', 'value' => '789'],
                ]
            )
        );

        $transactionData['documentNumber'] = '456';
        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION3_ID),
                $transactionData,
                [
                    'name' => 'John Doe',
                    'email' => 'user@oloy.com',
                    'nip' => 'aaa',
                    'phone' => $phoneNumber,
                    'loyaltyCardNumber' => 'sa2222',
                    'address' => [
                        'street' => 'Oxford Street',
                        'address1' => '12',
                        'city' => 'New York',
                        'country' => 'US',
                        'province' => 'New York',
                        'postal' => '10001',
                    ],
                ],
                $items,
                null,
                null,
                null,
                null,
                null,
                [
                    ['key' => 'scan_id', 'value' => '111111'],
                ]
            )
        );

        $transactionData['documentNumber'] = '789';
        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION4_ID),
                $transactionData,
                [
                    'name' => 'John Doe',
                    'email' => 'user-temp@oloy.com',
                    'nip' => 'aaa',
                    'phone' => $phoneNumber,
                    'loyaltyCardNumber' => 'sa2222',
                    'address' => [
                        'street' => 'Oxford Street',
                        'address1' => '12',
                        'city' => 'New York',
                        'country' => 'US',
                        'province' => 'New York',
                        'postal' => '10001',
                    ],
                ],
                $items
            )
        );

        $transactionData['documentNumber'] = '999';
        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION6_ID),
                $transactionData,
                [
                    'name' => 'John Doe',
                    'email' => 'o@lo.com',
                    'nip' => 'aaa',
                    'phone' => '123',
                    'loyaltyCardNumber' => 'sa21as222',
                    'address' => [
                        'street' => 'Oxford Street',
                        'address1' => '12',
                        'city' => 'New York',
                        'country' => 'US',
                        'province' => 'New York',
                        'postal' => '10001',
                    ],
                ],
                $items
            )
        );

        $transactionData['documentNumber'] = 'labels-test-transaction';
        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION7_ID),
                $transactionData,
                [
                    'name' => 'John Doe',
                    'email' => LoadUserData::USER_USERNAME,
                    'nip' => 'aaa',
                    'phone' => '123',
                    'loyaltyCardNumber' => 'sa21as222',
                    'address' => [
                        'street' => 'Oxford Street',
                        'address1' => '12',
                        'city' => 'New York',
                        'country' => 'US',
                        'province' => 'New York',
                        'postal' => '10001',
                    ],
                ],
                [],
                null,
                null,
                null,
                null,
                null,
                [
                    ['key' => 'existing label', 'value' => 'some value'],
                ]
            )
        );

        $transactionData['documentNumber'] = 'coupons-test-transaction';
        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION_COUPONS_ID),
                $transactionData,
                [
                    'name' => 'John Doe',
                    'email' => LoadUserData::USER_COUPON_RETURN_USERNAME,
                    'nip' => 'aaa',
                    'phone' => '123',
                    'loyaltyCardNumber' => 'sa21as222',
                    'address' => [
                        'street' => 'Oxford Street',
                        'address1' => '12',
                        'city' => 'New York',
                        'country' => 'US',
                        'province' => 'New York',
                        'postal' => '10001',
                    ],
                ],
                [
                    [
                        'sku' => ['code' => 'SKU1'],
                        'name' => 'item 1',
                        'quantity' => 1,
                        'grossValue' => 1000,
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
                ],
                null,
                null,
                null,
                null,
                null,
                [
                    ['key' => 'existing label', 'value' => 'some value'],
                ]
            )
        );

        $this->loadTransactionForCouponUsage($bus);
        $this->loadTransactionReturn($bus);
    }

    /**
     * @param CommandBus $bus
     */
    private function loadTransactionReturn(CommandBus $bus): void
    {
        $transactionData = [
            'documentNumber' => '20181101',
            'purchasePlace' => 'New York',
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

        /** @var CommandBus $bus */
        $customerData = [
            'name' => 'John Doe',
            'email' => 'ol@oy.com',
            'nip' => 'aaa',
            'phone' => '',
            'loyaltyCardNumber' => '222',
            'address' => [
                'street' => 'Oxford Street',
                'address1' => '12',
                'city' => 'New York',
                'country' => 'US',
                'province' => 'New York',
                'postal' => '10001',
            ],
        ];

        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION8_ID),
                $transactionData,
                $customerData,
                $items,
                new PosId(LoadPosData::POS_ID),
                null,
                null,
                null,
                null,
                [
                    ['key' => 'scan_id', 'value' => 'abc123789def-abc123789def-abc123789def-abc123789def'],
                ]
            )
        );

        $transactionData2 = [
            'documentNumber' => '201811011023',
            'purchasePlace' => 'New York',
            'purchaseDate' => (new \DateTime('+1 day'))->getTimestamp(),
            'documentType' => 'return',
        ];

        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION9_ID),
                $transactionData2,
                $customerData,
                $items,
                new PosId(LoadPosData::POS_ID),
                null,
                null,
                null,
                '20181101',
                [
                    ['key' => 'scan_id', 'value' => 'abc123789def-abc123789def-abc123789def-abc123789def'],
                ]
            )
        );
    }

    /**
     * @param CommandBus $bus
     */
    private function loadTransactionForCouponUsage(CommandBus $bus): void
    {
        $transactionData = [
                'documentNumber' => '12355-coupons',
                'documentType' => 'sell',
                'purchaseDate' => (new \DateTime('2015-01-01'))->getTimestamp(),
                'purchasePlace' => 'New York',
        ];
        $items = [
                0 => [
                    'sku' => ['code' => '123'],
                    'name' => 'sku',
                    'quantity' => 1,
                    'grossValue' => 20,
                    'category' => 'test',
                    'maker' => 'company',
                ],
                1 => [
                    'sku' => ['code' => '1123'],
                    'name' => 'sku',
                    'quantity' => 1,
                    'grossValue' => 100,
                    'category' => 'test',
                    'maker' => 'company',
                ],
            ];
        $customerData = [
                'name' => 'John Doe',
                'email' => LoadUserData::USER_COUPON_RETURN_USERNAME,
                'nip' => 'aaa',
                'phone' => '+48123123000',
                'loyaltyCardNumber' => 'not-present-in-system',
                'address' => [
                    'street' => 'Oxford Street',
                    'address1' => '12',
                    'city' => 'New York',
                    'country' => 'US',
                    'province' => 'New York',
                    'postal' => '10001',
                ],
        ];

        $bus->dispatch(
            new RegisterTransaction(
                new TransactionId(self::TRANSACTION_COUPONS_USED_ID),
                $transactionData,
                $customerData,
                $items,
                null,
                null,
                null,
                null,
                null,
                []
            )
        );
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }
}
