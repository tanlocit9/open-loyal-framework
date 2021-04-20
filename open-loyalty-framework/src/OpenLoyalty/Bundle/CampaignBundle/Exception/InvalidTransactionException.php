<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Exception;

/**
 * Class TransactionRequiredException.
 */
class InvalidTransactionException extends CampaignLimitException
{
    protected $message = 'campaign.invalid_transaction';
}
