<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Exception;

/**
 * Class TooLowCouponValueException.
 */
class TooLowCouponValueException extends CampaignLimitException
{
    protected $message = 'campaign.too_low_coupon_value';
}
