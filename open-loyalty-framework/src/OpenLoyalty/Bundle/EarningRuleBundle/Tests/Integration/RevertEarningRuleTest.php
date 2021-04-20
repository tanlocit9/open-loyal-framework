<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Tests\Integration;

use OpenLoyalty\Bundle\CampaignBundle\DataFixtures\ORM\LoadCampaignData;
use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;

/**
 * Class RevertEarningRuleTest.
 */
class RevertEarningRuleTest extends BaseApiTest
{
    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    public function setUp()
    {
        parent::setUp();
        static::bootKernel();

        /* @var CustomerDetailsRepository $repo */
        $this->customerDetailsRepository = static::$kernel->getContainer()->get(CustomerDetailsRepository::class);
    }

    /**
     * @test
     * @dataProvider transactionDataProvider
     *
     * @param array $transactionData
     * @param array $revertTransactionData
     */
    public function it_should_revert_instant_reward(array $transactionData, array $revertTransactionData): void
    {
        /** @var CustomerDetails $customer */
        $customer = $this->customerDetailsRepository->find(LoadUserData::USER_USER_ID);
        $initialCount = count($customer->getCampaignPurchases());

        $transactionId = $this->sendTransactionData($transactionData);
        $this->sendTransactionData($revertTransactionData);

        /** @var CustomerDetails $customer */
        $customer = $this->customerDetailsRepository->find(LoadUserData::USER_USER_ID);
        $this->assertGreaterThan($initialCount, $customer->getCampaignPurchases());

        $purchaseFound = false;
        foreach ($customer->getCampaignPurchases() as $campaignPurchase) {
            if ($campaignPurchase->getCampaignId()->__toString() === LoadCampaignData::PERCENTAGE_COUPON_CAMPAIGN_ID
                && $campaignPurchase->getTransactionId()->__toString() === $transactionId
                && $campaignPurchase->getCoupon()->getCode() === '10') {
                $purchaseFound = true;
                $this->assertEquals(CampaignPurchase::STATUS_CANCELLED, $campaignPurchase->getStatus());
            }
        }

        $this->assertTrue($purchaseFound);
    }

    /**
     * @return array
     */
    public function transactionDataProvider(): array
    {
        return [
            [$this->getTransactionData(), $this->getReturnTransactionData()],
        ];
    }

    /**
     * @return array
     */
    private function getTransactionData(): array
    {
        return [
            'transactionData' => [
                'documentNumber' => '123456789',
                'documentType' => 'sell',
                'purchaseDate' => (new \DateTime('-10 day'))->format('Y-m-d'),
            ],
            'items' => [
                0 => [
                    'sku' => ['code' => '1'],
                    'name' => 'sku',
                    'quantity' => 1,
                    'grossValue' => 100,
                    'category' => 'test',
                    'maker' => 'company',
                ],
            ],
            'customerData' => [
                'name' => 'Jan Nowak',
                'email' => 'user@oloy.com',
                'phone' => '+48123123123',
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
    }

    /**
     * @return array
     */
    private function getReturnTransactionData(): array
    {
        return [
            'transactionData' => [
                'documentNumber' => '1234567891',
                'documentType' => 'return',
                'purchaseDate' => (new \DateTime('-9 day'))->format('Y-m-d'),
            ],
            'items' => [
                0 => [
                    'sku' => ['code' => '1'],
                    'name' => 'sku',
                    'quantity' => 1,
                    'grossValue' => 100,
                    'category' => 'test',
                    'maker' => 'company',
                ],
            ],
            'customerData' => [
                'name' => 'Jan Nowak',
                'email' => 'user@oloy.com',
                'phone' => '+48123123123',
                'address' => [
                    'street' => 'Bagno',
                    'address1' => '12',
                    'city' => 'Warszawa',
                    'country' => 'PL',
                    'province' => 'Mazowieckie',
                    'postal' => '00-800',
                ],
            ],
            'revisedDocument' => '123456789',
        ];
    }

    /**
     * @param $data
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
