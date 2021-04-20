<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Form\DataTransformer;

use OpenLoyalty\Bundle\SettingsBundle\Entity\IntegerSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class IntegerSettingDataTransformer.
 */
class IntegerSettingDataTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var SettingsManager
     */
    protected $settingsManager;

    /**
     * StringSettingDataTransformer constructor.
     *
     * @param string          $key
     * @param SettingsManager $settingsManager
     */
    public function __construct($key, SettingsManager $settingsManager)
    {
        $this->key = $key;
        $this->settingsManager = $settingsManager;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value == null) {
            return;
        }
        if (!$value instanceof IntegerSettingEntry) {
            throw new \InvalidArgumentException(
                sprintf('Value %s is not an instance of %s', $value, IntegerSettingEntry::class)
            );
        }

        return $value->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        $entry = $this->settingsManager->getSettingByKey($this->key);
        if (!$entry) {
            $entry = new IntegerSettingEntry($this->key);
        }
        $entry->setValue($value);

        return $entry;
    }
}
