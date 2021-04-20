<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Coupon;

use OpenLoyalty\Bundle\CampaignBundle\Exception\CampaignLimitException;
use OpenLoyalty\Bundle\CampaignBundle\Exception\NoCouponsLeftException;
use OpenLoyalty\Bundle\CampaignBundle\Exception\TooLowCouponValueException;
use OpenLoyalty\Bundle\CampaignBundle\Service\CampaignProvider;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;

/**
 * Class CouponCodeProvider.
 */
class CouponCodeProvider
{
    /**
     * @var CampaignProvider
     */
    private $campaignProvider;

    /**
     * CouponCodeProvider constructor.
     *
     * @param CampaignProvider $campaignProvider
     */
    public function __construct(CampaignProvider $campaignProvider)
    {
        $this->campaignProvider = $campaignProvider;
    }

    /**
     * @param Campaign $campaign
     * @param float    $transactionValue
     *
     * @return Coupon
     *
     * @throws CampaignLimitException
     * @throws NoCouponsLeftException
     * @throws TooLowCouponValueException
     */
    public function getCoupon(Campaign $campaign, float $transactionValue = 0): Coupon
    {
        if ($campaign->getReward() === Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE) {
            $couponPercentage = $campaign->getTransactionPercentageValue();
            $couponValue = round($transactionValue * $couponPercentage / 100, 0);

            if ($couponValue <= 0.0) {
                throw new TooLowCouponValueException();
            }

            return new Coupon((string) $couponValue);
        }

        $freeCoupons = $this->campaignProvider->getFreeCoupons($campaign);
        if ($campaign->isSingleCoupon()) {
            $freeCoupons = $this->campaignProvider->getAllCoupons($campaign);
        }

        if (count($freeCoupons) === 0) {
            throw new NoCouponsLeftException();
        }

        return new Coupon(reset($freeCoupons));
    }
}
