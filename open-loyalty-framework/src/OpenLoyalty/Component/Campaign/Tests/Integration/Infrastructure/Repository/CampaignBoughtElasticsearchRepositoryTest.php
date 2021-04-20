<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Tests\Integration\Infrastructure\Repository;

use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBoughtRepository;
use OpenLoyalty\Component\Campaign\Infrastructure\Repository\CampaignBoughtElasticsearchRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CampaignBoughtElasticsearchRepositoryTest.
 */
final class CampaignBoughtElasticsearchRepositoryTest extends KernelTestCase
{
    private const CAMPAIGN_ID = '000096cf-32a3-43bd-9034-4df343e5fd94';
    private const CUSTOMER_ID = '00000000-0000-474c-b092-b0dd880c07aa';
    private const COUPON_CODE = '200';
    private const COUPON_ID = 'b794ef57-62b1-4ec7-97c9-3b7c9926df70';
    private const TRANSACTION_ID = '00000000-0000-1111-0000-000000002121';

    /**
     * @var CampaignBoughtElasticsearchRepository
     */
    private $campaignBoughtRepository;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        $this->campaignBoughtRepository = self::$kernel->getContainer()->get(CampaignBoughtRepository::class);
    }

    /**
     * @test
     */
    public function it_returns_campaign_items_without_date_range_filter()
    {
        $items = $this->campaignBoughtRepository->findByParametersPaginated([]);
        $this->assertGreaterThan(0, count($items));
    }

    /**
     * @test
     */
    public function it_returns_campaign_items_with_date_range_filter_from_future()
    {
        $items = $this->campaignBoughtRepository->findByParametersPaginated(['purchasedAtFrom' => date('Y-m-d H:i:s', strtotime('+ 2 years'))]);
        $this->assertLessThanOrEqual(0, count($items));
    }

    /**
     * @test
     */
    public function it_returns_campaign_items_with_date_range_filter()
    {
        $items = $this->campaignBoughtRepository->findByParametersPaginated(['purchasedAtTo' => date('Y-m-d H:i:s', strtotime('+ 2 years'))]);
        $this->assertLessThanOrEqual(2, count($items));
    }

    /**
     * @test
     */
    public function it_returns_campaign_bought(): void
    {
        $this->addCampaignPurchase();

        $campaign = $this->campaignBoughtRepository->findOneByCouponId(self::COUPON_ID);

        $this->assertSame(self::COUPON_ID, $campaign->getCoupon()->getId());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_campaign_bought_not_found(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $campaignBoughtId = 'non-exists-id';
        $this->campaignBoughtRepository->findOneByCouponId($campaignBoughtId);
    }

    /**
     * @throws \Exception
     */
    private function addCampaignPurchase(): void
    {
        $data = [
            'campaignId' => self::CAMPAIGN_ID,
            'customerId' => self::CUSTOMER_ID,
            'transactionId' => self::TRANSACTION_ID,
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
        $this->campaignBoughtRepository->save(CampaignBought::deserialize($data));
    }
}
