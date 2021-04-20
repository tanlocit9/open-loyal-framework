<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyaltyPlugin\SalesManagoBundle\Config;

use OpenLoyalty\Bundle\SettingsBundle\Config\ConfigInterface;
use OpenLoyaltyPlugin\SalesManagoBundle\Form\Type\ConfigFormType;

/**
 * Class Config.
 */
class Config implements ConfigInterface
{
    const KEY = 'sales_manago';

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return self::KEY;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'SalesManago';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsConfig(): array
    {
        return [
            'api_url' => 'text',
            'api_secret' => 'text',
            'api_key' => 'text',
            'customer_id' => 'text',
            'email' => 'text',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormClassName(): string
    {
        return ConfigFormType::class;
    }
}
