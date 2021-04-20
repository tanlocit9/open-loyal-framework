<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Config;

/**
 * Interface ConfigInterface.
 */
interface ConfigInterface
{
    /**
     * @return string
     */
    public function getKey(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return array
     */
    public function getSettingsConfig(): array;

    /**
     * @return string
     */
    public function getFormClassName(): string;
}
