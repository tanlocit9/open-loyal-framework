<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Service;

use OpenLoyalty\Bundle\SettingsBundle\Form\Type\SettingsFormType;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Customer\Infrastructure\Exception\LevelDowngradeModeNotSupportedException;
use OpenLoyalty\Component\Customer\Infrastructure\LevelDowngradeModeProvider;

/**
 * Class SettingsBasedLevelDowngradeModeProvider.
 */
class SettingsBasedLevelDowngradeModeProvider implements LevelDowngradeModeProvider
{
    /**
     * @var SettingsManager
     */
    protected $settingsManager;

    /**
     * SettingsBasedLevelDowngradeModeProvider constructor.
     *
     * @param SettingsManager $settingsManager
     */
    public function __construct(SettingsManager $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getMode(): string
    {
        $mode = $this->settingsManager->getSettingByKey(SettingsFormType::LEVEL_DOWNGRADE_MODE_SETTINGS_KEY);
        if (!$mode) {
            return LevelDowngradeModeProvider::MODE_AUTO;
        }

        $value = $mode->getValue();

        if (in_array($value, [
            LevelDowngradeModeProvider::MODE_X_DAYS,
            LevelDowngradeModeProvider::MODE_AUTO,
            LevelDowngradeModeProvider::MODE_NONE,
        ])) {
            return $value;
        }

        throw new LevelDowngradeModeNotSupportedException();
    }

    /**
     * {@inheritdoc}
     */
    public function getBase(): string
    {
        $base = $this->settingsManager->getSettingByKey(SettingsFormType::LEVEL_DOWNGRADE_BASE_SETTINGS_KEY);
        if (!$base) {
            return LevelDowngradeModeProvider::BASE_ACTIVE_POINTS;
        }

        $value = $base->getValue();

        if (in_array($value, [
            LevelDowngradeModeProvider::BASE_NONE,
            LevelDowngradeModeProvider::BASE_ACTIVE_POINTS,
            LevelDowngradeModeProvider::BASE_EARNED_POINTS,
            LevelDowngradeModeProvider::BASE_EARNED_POINTS_SINCE_LAST_LEVEL_CHANGE,
        ])) {
            return $value;
        }

        throw new LevelDowngradeModeNotSupportedException();
    }

    /**
     * {@inheritdoc}
     */
    public function getDays(): int
    {
        $days = $this->settingsManager->getSettingByKey('levelDowngradeDays');
        if (!$days) {
            return self::DEFAULT_DAYS;
        }

        return $days->getValue() ?? self::DEFAULT_DAYS;
    }

    /**
     * {@inheritdoc}
     */
    public function isResettingPointsEnabled(): bool
    {
        $enabled = $this->settingsManager->getSettingByKey('levelResetPointsOnDowngrade');

        return $enabled ? $enabled->getValue() : false;
    }
}
