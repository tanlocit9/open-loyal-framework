<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Service;

/**
 * Interface LocaleProviderInterface.
 */
interface LocaleProviderInterface
{
    /**
     * Current request locale assigned by symfony listener (depends on routing).
     *
     * @return string
     */
    public function getCurrentLocale(): string;

    /**
     * Configuration default locale defined in translations settings as default translation.
     *
     * @return string
     */
    public function getConfigurationDefaultLocale(): string;

    /**
     * Internal default value defined in configuration.
     *
     * @return string
     */
    public function getDefaultLocale(): string;

    /**
     * Available locales defined in translations settings.
     *
     * @return array
     */
    public function getAvailableLocales(): array;
}
