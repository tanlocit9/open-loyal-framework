<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Exception;

/**
 * Class NotAllowedException.
 */
class NotAllowedException extends \Exception
{
    protected $message = 'campaign.not_allowed';
}
