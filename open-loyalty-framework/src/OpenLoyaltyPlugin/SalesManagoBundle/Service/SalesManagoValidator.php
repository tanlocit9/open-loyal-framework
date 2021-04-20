<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyaltyPlugin\SalesManagoBundle\Service;

use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyaltyPlugin\SalesManagoBundle\Config\Config;

/**
 * Class SalesManagoValidator.
 */
class SalesManagoValidator
{
    /**
     * @var SettingsManager
     */
    private $settings;

    /**
     * SalesManagoValidator constructor.
     *
     * @param SettingsManager $manager
     */
    public function __construct(SettingsManager $manager)
    {
        $this->settings = $manager;
    }

    /**
     * @return bool
     */
    public function verifySalesManagoEnabled()
    {
        try {
            $config = $this->settings->getSettingByKey('marketingVendorsValue');
        } catch (\Exception $e) {
            return false;
        }
        if ($config->getValue() === Config::KEY) {
            return true;
        }

        return false;
    }
}
