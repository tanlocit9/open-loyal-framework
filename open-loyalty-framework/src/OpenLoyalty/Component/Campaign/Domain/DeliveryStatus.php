<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Domain;

use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Campaign\Domain\Exception\DeliveryStatusException;

/**
 * Class DeliveryStatus.
 */
class DeliveryStatus
{
    /**
     * @var string|null
     */
    private $status = null;

    /**
     * DeliveryStatus constructor.
     *
     * @param string $status
     */
    public function __construct(string $status = null)
    {
        $availableStatuses = [
            null,
            CampaignBought::DELIVERY_STATUS_CANCELED,
            CampaignBought::DELIVERY_STATUS_DELIVERED,
            CampaignBought::DELIVERY_STATUS_ORDERED,
            CampaignBought::DELIVERY_STATUS_SHIPPED,
        ];
        if (!in_array($status, $availableStatuses)) {
            throw DeliveryStatusException::create();
        }

        $this->status = $status;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->status;
    }
}
