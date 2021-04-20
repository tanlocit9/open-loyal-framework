<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TranslationBundle\Provider;

use A2lix\TranslationFormBundle\Locale\LocaleProviderInterface;
use OpenLoyalty\Bundle\SettingsBundle\Service\LocaleProviderInterface as OpenLoyaltyLocaleProvider;

/**
 * Class LocaleProvider.
 */
class A2lixLocaleProvider implements LocaleProviderInterface
{
    /**
     * @var OpenLoyaltyLocaleProvider
     */
    protected $localeProvider;

    /**
     * A2lixLocaleProvider constructor.
     *
     * @param OpenLoyaltyLocaleProvider $localeProvider
     */
    public function __construct(OpenLoyaltyLocaleProvider $localeProvider)
    {
        $this->localeProvider = $localeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales(): array
    {
        return $this->localeProvider->getAvailableLocales();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultLocale(): string
    {
        return $this->localeProvider->getConfigurationDefaultLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredLocales(): array
    {
        return [$this->getDefaultLocale()];
    }
}
