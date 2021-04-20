<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Service;

use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class UserSettingsManager.
 */
class UserSettingsManager
{
    /**
     * @var UserSettingsManagerInterface
     */
    private $persistence;

    /**
     * UserSettingsManager constructor.
     *
     * @param UserSettingsManagerInterface $persistence
     */
    public function __construct(UserSettingsManagerInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    /**
     * @return array
     */
    public function getPushyTokens(): array
    {
        $pushyTokens = $this->persistence->getSettingByKey('pushyToken');

        if (is_null($pushyTokens)) {
            return [];
        }

        return json_decode($pushyTokens->getValue(), true) ?? [];
    }

    /**
     * @param array
     */
    public function setPushyTokens(array $pushyTokens): void
    {
        $setting = $this->persistence->getSettingByKey('pushyToken');

        if (is_null($setting)) {
            $setting = $this->persistence->createSetting('pushyToken');
        }

        $setting->setValue(json_encode(array_values($pushyTokens)));
        $this->persistence->save([$setting], true);
    }

    public function setUserId(CustomerId $customerId): void
    {
        $this->persistence->setUserId($customerId);
    }
}
