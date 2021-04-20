<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TranslationBundle\Provider;

use OpenLoyalty\Bundle\SettingsBundle\Service\LocaleProviderInterface;

/**
 * Class DefaultLocaleCallable.
 */
class DefaultLocaleCallable
{
    /**
     * @var LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * @param LocaleProviderInterface $localeProvider
     */
    public function setLocaleProvider(LocaleProviderInterface $localeProvider)
    {
        $this->localeProvider = $localeProvider;
    }

    /**
     * @return string
     */
    public function __invoke()
    {
        return $this->localeProvider->getConfigurationDefaultLocale();
    }
}
