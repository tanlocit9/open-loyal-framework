<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBoughtRepository;

/**
 * Class CampaignBoughtControllerTest.
 */
final class CampaignBoughtControllerTest extends BaseApiTest
{
    private const CAMPAIGN_ID = '000096cf-32a3-43bd-9034-4df343e5fd94';
    private const CUSTOMER_ID = '00000000-0000-474c-b092-b0dd880c07aa';
    private const COUPON_CODE = '200';
    private const COUPON_ID = 'b794ef57-62b1-4ec7-97c9-3b7c9926df70';

    /**
     * @var string
     */
    private $campaignBoughtId;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        $this->campaignBoughtId = CampaignBought::createIdFromString(
            self::CAMPAIGN_ID,
            self::CUSTOMER_ID,
            self::COUPON_CODE
        );
        $this->addCampaignPurchase();
    }

    /**
     * @test
     */
    public function it_change_delivery_status_to_canceled(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'PUT',
            sprintf('/api/admin/customer/%s/bought/coupon/%s/changeDeliveryStatus', self::CUSTOMER_ID, self::COUPON_ID),
            [
                'deliveryStatus' => ['status' => CampaignBought::DELIVERY_STATUS_CANCELED],
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_error_when_data_is_invalid(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'PUT',
            sprintf('/api/admin/customer/%s/bought/coupon/%s/changeDeliveryStatus', self::CUSTOMER_ID, self::COUPON_ID),
            [
                'deliveryStatus' => '',
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Add Campaign purchase.
     */
    private function addCampaignPurchase(): void
    {
        $data = [
            'campaignId' => self::CAMPAIGN_ID,
            'customerId' => self::CUSTOMER_ID,
            'purchasedAt' => (new \DateTime())->getTimestamp(),
            'coupon' => self::COUPON_CODE,
            'couponId' => self::COUPON_ID,
            'campaignType' => 'discount_code',
            'campaignName' => 'Percentage discount code',
            'customerEmail' => 'user-temp@oloy.com',
            'customerPhone' => '+48345345000',
            'campaignShippingAddressStreet' => 'Example Street',
            'campaignShippingAddressAddress1' => '1',
            'campaignShippingAddressAddress2' => '2',
            'campaignShippingAddressPostal' => '00-000',
            'campaignShippingAddressCity' => 'Wroclaw',
            'campaignShippingAddressProvince' => 'Dolnoslaskie',
            'campaignShippingAddressCountry' => 'Poland',
            'used' => false,
        ];
        self::$kernel->getContainer()->get(CampaignBoughtRepository::class)->save(CampaignBought::deserialize($data));
    }
}
