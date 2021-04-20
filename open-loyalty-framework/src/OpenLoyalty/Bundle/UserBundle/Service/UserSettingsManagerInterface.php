<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Service;

use OpenLoyalty\Bundle\UserBundle\Entity\UserSettingsEntry;
use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Interface UserSettingsManagerInterface.
 */
interface UserSettingsManagerInterface
{
    /**
     * @param array $settings
     * @param bool  $flush
     */
    public function save(array $settings, $flush = true): void;

    /**
     * Remove all settings relating to the currently set user.
     */
    public function removeAll(): void;

    /**
     * @return array
     */
    public function getSettings(): array;

    /**
     * @param string $key
     *
     * @return UserSettingsEntry|null
     */
    public function getSettingByKey(string $key): ?UserSettingsEntry;

    /**
     * @param string $key
     */
    public function removeSettingByKey(string $key): void;

    /**
     * @return CustomerId
     */
    public function getUserId(): CustomerId;

    /**
     * @param CustomerId $customerId
     */
    public function setUserId(CustomerId $customerId): void;

    /**
     * @param string $key
     *
     * @return UserSettingsEntry
     */
    public function createSetting(string $key): UserSettingsEntry;
}
