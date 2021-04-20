<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;

/**
 * Class CountryChoices.
 */
class DeliveryStatusChoices implements ChoiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        $choices = [
            CampaignBought::DELIVERY_STATUS_ORDERED,
            CampaignBought::DELIVERY_STATUS_SHIPPED,
            CampaignBought::DELIVERY_STATUS_DELIVERED,
            CampaignBought::DELIVERY_STATUS_CANCELED,
        ];

        return ['choices' => $choices];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'deliveryStatus';
    }
}
