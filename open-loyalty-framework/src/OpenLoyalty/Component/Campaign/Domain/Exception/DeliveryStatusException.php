<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Domain\Exception;

/**
 * Class CampaignRewardsStatusException.
 */
class DeliveryStatusException extends \DomainException
{
    /**
     * @return DeliveryStatusException
     */
    public static function create(): self
    {
        return new self('Given status is incorrect!');
    }
}
