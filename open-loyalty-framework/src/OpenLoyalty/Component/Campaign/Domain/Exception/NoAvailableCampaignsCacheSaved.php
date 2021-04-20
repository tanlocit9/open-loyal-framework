<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Exception;

/**
 * Class NoAvailableCampaignsCacheSaved.
 */
class NoAvailableCampaignsCacheSaved extends \DomainException
{
    /**
     * @return NoAvailableCampaignsCacheSaved
     */
    public static function create(): self
    {
        return new self('No cache! No notifications will be sent this time.');
    }
}
