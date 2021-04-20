<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

use OpenLoyalty\Bundle\SettingsBundle\Service\TranslationsProvider;

/**
 * Class AvailableFrontendTranslationsChoices.
 */
class AvailableFrontendTranslationsChoices implements ChoiceProvider
{
    /**
     * @var TranslationsProvider
     */
    protected $provider;

    /**
     * AvailableFrontendTranslationsChoices constructor.
     *
     * @param TranslationsProvider $provider
     */
    public function __construct(TranslationsProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        $availableTranslationsList = $this->provider->getAvailableTranslationsList();

        return ['choices' => $availableTranslationsList];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'availableFrontendTranslations';
    }
}
