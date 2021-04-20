<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

use OpenLoyalty\Bundle\SettingsBundle\Config\ConfigInterface;

/**
 * Class AvailableMarketingVendors.
 */
class AvailableMarketingVendors implements ChoiceProvider
{
    const NONE = 'none';

    /**
     * @var array
     */
    protected $vendors = [];

    /**
     * @param ConfigInterface $vendor
     */
    public function addVendor(ConfigInterface $vendor)
    {
        $this->vendors[] = $vendor;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        $choices = [
            self::NONE => [
                'name' => 'Disabled',
                'config' => [],
            ],
        ];

        foreach ($this->vendors as $vendor) {
            $choices[$vendor->getKey()] = [
                'name' => $vendor->getName(),
                'config' => $vendor->getSettingsConfig(),
            ];
        }

        return [
            'choices' => $choices,
        ];
    }

    /**
     * @param string $vendorName
     *
     * @return string
     */
    public function getVendorFormClassName(string $vendorName): string
    {
        $vendor = array_filter($this->vendors, function ($vendor) use ($vendorName) {
            /* @var ConfigInterface $vendor */
            return ($vendor->getKey() == $vendorName) ? $vendor : false;
        });

        $vendor = reset($vendor);

        if (!$vendor instanceof ConfigInterface) {
            return '';
        }

        return $vendor->getFormClassName();
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'availableMarketingVendors';
    }
}
