<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Exception;

/**
 * Class CampaignLimitPerCustomerExceededException.
 */
class CampaignLimitPerCustomerExceededException extends CampaignLimitException
{
    protected $message = 'campaign.limit_per_customer_exceeded';
}
