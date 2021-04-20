<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Bundle\EarningRuleBundle\Tests\Integration;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\PosBundle\DataFixtures\ORM\LoadPosData;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetails;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetailsRepository;

/**
 * Class ApplyingEarningRulesTest.
 */
final class ApplyingEarningRulesTest extends BaseApiTest
{
    /**
     * @var PointsTransferDetailsRepository
     */
    private $pointsTransferDetailsRepository;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        static::bootKernel();

        $this->pointsTransferDetailsRepository = static::$kernel->getContainer()->get(PointsTransferDetailsRepository::class);
    }

    /**
     * @test
     */
    public function it_adds_points_after_transaction(): void
    {
        $formData = [
            'transactionData' => [
                'documentNumber' => '123222',
                'documentType' => 'sell',
                'purchaseDate' => (new \DateTime('+1 day'))->format('Y-m-d'),
                'purchasePlace' => 'wroclaw',
            ],
            'items' => [
                0 => [
                    'sku' => ['code' => '12113'],
                    'name' => 'sku',
                    'quantity' => 1,
                    'grossValue' => 3,
                    'category' => 'test',
                    'maker' => 'company',
                ],
                1 => [
                    'sku' => ['code' => '11223233'],
                    'name' => 'sku',
                    'quantity' => 1,
                    'grossValue' => 20,
                    'category' => 'test',
                    'maker' => 'company',
                ],
                2 => [
                    'sku' => ['code' => 'SKU123'],
                    'name' => 'sku',
                    'quantity' => 1,
                    'grossValue' => 20,
                    'category' => 'test',
                    'maker' => 'company',
                ],
            ],
            'customerData' => [
                'name' => 'Jan Nowak',
                'email' => 'user-temp@oloy.com',
                'nip' => 'aaa',
                'phone' => '+48123123123',
                'loyaltyCardNumber' => 'not-present-in-system',
                'address' => [
                    'street' => 'Bagno',
                    'address1' => '12',
                    'city' => 'Warszawa',
                    'country' => 'PL',
                    'province' => 'Mazowieckie',
                    'postal' => '00-800',
                ],
            ],
        ];

        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/transaction',
            [
                'transaction' => $formData,
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        static::$kernel->boot();
        /** @var PointsTransferDetailsRepository $repo */
        $repo = static::$kernel->getContainer()->get('oloy.points.account.repository.points_transfer_details');
        /** @var PointsTransferDetails $points */
        $points = $repo->findBy(['transactionId' => $data['transactionId']]);

        $this->assertTrue(count($points) > 0);
        $points = reset($points);
        $this->assertEquals(144.9, $points->getValue(), 'There should be 144.9 points for this transaction, but there are '.$points->getValue());
    }

    /**
     * @test
     */
    public function it_adds_points_after_transaction_with_pos(): void
    {
        $formData = [
            'transactionData' => [
                'documentNumber' => '1231111_1',
                'documentType' => 'sell',
                'purchaseDate' => (new \DateTime('+1 day'))->format('Y-m-d'),
                'purchasePlace' => 'wroclaw',
            ],
            'items' => [
                0 => [
                    'sku' => ['code' => '12113'],
                    'name' => 'sku',
                    'quantity' => 1,
                    'grossValue' => 3,
                    'category' => 'test',
                    'maker' => 'company',
                ],
                1 => [
                    'sku' => ['code' => '11223233'],
                    'name' => 'sku',
                    'quantity' => 1,
                    'grossValue' => 20,
                    'category' => 'test',
                    'maker' => 'company',
                ],
                2 => [
                    'sku' => ['code' => 'SKU123'],
                    'name' => 'sku',
                    'quantity' => 1,
                    'grossValue' => 20,
                    'category' => 'test',
                    'maker' => 'company',
                ],
            ],
            'customerData' => [
                'name' => 'Jan Nowak',
                'email' => 'user-temp@oloy.com',
                'nip' => 'aaa',
                'phone' => '+48123123123',
                'loyaltyCardNumber' => 'not-present-in-system',
                'address' => [
                    'street' => 'Bagno',
                    'address1' => '12',
                    'city' => 'Warszawa',
                    'country' => 'PL',
                    'province' => 'Mazowieckie',
                    'postal' => '00-800',
                ],
            ],
            'pos' => LoadPosData::POS_ID,
        ];

        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/transaction',
            [
                'transaction' => $formData,
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        static::$kernel->boot();
        /** @var PointsTransferDetailsRepository $repo */
        $repo = static::$kernel->getContainer()->get('oloy.points.account.repository.points_transfer_details');
        /** @var PointsTransferDetails $points */
        $points = $repo->findBy(['transactionId' => $data['transactionId']]);

        $this->assertTrue(count($points) > 0);
        $points = reset($points);
        $this->assertEquals(98.9, $points->getValue(), 'There should be 98.9 points for this transaction, but there are '.$points->getValue());
    }

    /**
     * @test
     */
    public function it_adds_points_after_calling_custom_event_limited_to_pos(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/v1/earnRule/test_event_limited_to_pos/customer/'.LoadUserData::USER2_USER_ID
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('points', $data);
        $this->assertEquals(88, $data['points']);
    }

    /**
     * @test
     */
    public function it_adds_points_after_calling_custom_event_by_a_customer(): void
    {
        $client = $this->createAuthenticatedClient(
            LoadUserData::USER_USERNAME,
            LoadUserData::USER_PASSWORD,
            'customer'
        );
        $client->request(
            'POST',
            '/api/customer/earnRule/facebook_like'
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('points', $data);
        $this->assertEquals(100, $data['points']);
    }

    /**
     * @test
     * @dataProvider getTransactionDataForSegmentEarningRule
     *
     * @param array $transactionArray
     */
    public function it_should_execute_earning_rule_with_segment(array $transactionArray): void
    {
        $transactionId = $this->sendTransactionData($transactionArray);

        /** @var PointsTransferDetails[] $pointsTransferDetailsList */
        $pointsTransferDetailsList = $this->pointsTransferDetailsRepository->findBy(
            [
                'customerId' => LoadUserData::USER1_USER_ID,
                'transactionId' => $transactionId,
            ]
        );

        $result = null;
        foreach ($pointsTransferDetailsList as $pointsTransferDetails) {
            if ($pointsTransferDetails->getComment() === 'General spending rule with segment') {
                $result = $pointsTransferDetails;
            }
        }

        $this->assertNotNull($result);
        $this->assertEquals(10000, $result->getValue());
    }

    /**
     * @return array
     */
    public function getTransactionDataForSegmentEarningRule(): array
    {
        return [
            [
                [
                    'transactionData' => [
                        'documentNumber' => '1230009922',
                        'documentType' => 'sell',
                        'purchaseDate' => (new \DateTime('+1 day'))->format('Y-m-d'),
                        'purchasePlace' => 'wroclaw',
                    ],
                    'items' => [
                        0 => [
                            'sku' => ['code' => '123'],
                            'name' => 'sku',
                            'quantity' => 1,
                            'grossValue' => 100,
                            'category' => 'test',
                            'maker' => 'company',
                        ],
                    ],
                    'customerData' => [
                        'name' => 'Jan Nowak',
                        'email' => 'user-1@oloy.com',
                        'nip' => 'aaa',
                        'phone' => '+48123123123',
                        'loyaltyCardNumber' => 'not-present-in-system',
                        'address' => [
                            'street' => 'Bagno',
                            'address1' => '12',
                            'city' => 'Warszawa',
                            'country' => 'PL',
                            'province' => 'Mazowieckie',
                            'postal' => '00-800',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array
     *
     * @return string
     */
    private function sendTransactionData(array $data): string
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/transaction',
            [
                'transaction' => $data,
            ]
        );

        return json_decode($client->getResponse()->getContent(), true)['transactionId'];
    }
}
