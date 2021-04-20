<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Integration\Domain\Coupon;

use OpenLoyalty\Bundle\CampaignBundle\Service\CampaignProvider;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Coupon\CouponCodeProvider;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CouponCodeProviderTest extends WebTestCase
{
    /**
     * @var CouponCodeProvider
     */
    private $couponCodeProvider;

    public function setUp()
    {
        parent::setUp();

        static::bootKernel();
        $campaignProvider = static::$kernel->getContainer()->get(CampaignProvider::class);

        $this->couponCodeProvider = new CouponCodeProvider($campaignProvider);
    }

    /**
     * @test
     */
    public function it_should_return_coupon_for_percentage_coupon_campaign()
    {
        $campaign = new Campaign(new CampaignId('3a40b784-913f-45ee-8646-a78b2b4f5cef'));
        $campaign->setTransactionPercentageValue(10);
        $campaign->setReward(Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE);

        $coupon = $this->couponCodeProvider->getCoupon($campaign, 100);

        $this->assertEquals('10', $coupon->getCode());
    }

    /**
     * @test
     */
    public function it_should_return_coupon_for_standard_campaign()
    {
        $campaign = new Campaign(new CampaignId('3a40b784-913f-45ee-8646-a78b2b4f5cef'));
        $campaign->setTransactionPercentageValue(10);
        $campaign->setReward(Campaign::REWARD_TYPE_DISCOUNT_CODE);
        $campaign->setCoupons([new Coupon('123')]);
        $coupon = $this->couponCodeProvider->getCoupon($campaign, 100);

        $this->assertEquals('123', $coupon->getCode());
    }
}
