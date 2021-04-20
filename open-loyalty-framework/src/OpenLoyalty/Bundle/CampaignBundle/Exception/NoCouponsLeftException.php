<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Exception;

/**
 * Class NoCouponsLeftException.
 */
class NoCouponsLeftException extends CampaignLimitException
{
    protected $message = 'campaign.no_coupons_left';
}
