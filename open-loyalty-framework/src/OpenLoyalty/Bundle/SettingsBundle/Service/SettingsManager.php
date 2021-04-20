<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\SettingsBundle\Service;

use OpenLoyalty\Bundle\SettingsBundle\Entity\SettingsEntry;
use OpenLoyalty\Bundle\SettingsBundle\Model\Settings;
use OpenLoyalty\Bundle\SettingsBundle\Exception\AlreadyExistException;

/**
 * Interface SettingsManager.
 */
interface SettingsManager
{
    /**
     * @param Settings $settings
     * @param bool     $flush
     *
     * @throws AlreadyExistException
     */
    public function save(Settings $settings, $flush = true): void;

    /**
     * Remove all.
     */
    public function removeAll(): void;

    /**
     * @return Settings
     */
    public function getSettings(): Settings;

    /**
     * @param string $key
     *
     * @return SettingsEntry|null
     */
    public function getSettingByKey(string $key): ?SettingsEntry;

    /**
     * @param string $key
     */
    public function removeSettingByKey(string $key): void;
}
