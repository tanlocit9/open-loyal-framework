<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Exception;

/**
 * Class TransactionRequiredException.
 */
class TransactionRequiredException extends CampaignLimitException
{
    protected $message = 'campaign.transaction_required';
}
