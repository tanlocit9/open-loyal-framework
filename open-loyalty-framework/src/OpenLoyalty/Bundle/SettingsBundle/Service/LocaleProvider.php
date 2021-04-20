<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Service;

use OpenLoyalty\Component\Translation\Domain\Language;
use OpenLoyalty\Component\Translation\Domain\LanguageRepository;

/**
 * Class LocaleProvider.
 */
class LocaleProvider implements LocaleProviderInterface
{
    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var LanguageRepository
     */
    protected $languageRepository;

    /**
     * A2lixLocaleProvider constructor.
     *
     * @param string             $defaultLocale
     * @param LanguageRepository $languageRepository
     */
    public function __construct(string $defaultLocale, LanguageRepository $languageRepository)
    {
        $this->defaultLocale = $defaultLocale;
        $this->languageRepository = $languageRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentLocale(): string
    {
        return $this->getConfigurationDefaultLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationDefaultLocale(): string
    {
        $default = $this->languageRepository->getDefault();

        return $default ? $default->getCode() : $this->getDefaultLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLocales(): array
    {
        $languages = $this->languageRepository->findAll();
        $locales = array_map(
            function (Language $language) {
                return $language->getCode();
            },
            $languages
        );

        return $locales;
    }
}
