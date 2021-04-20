<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UtilityBundle\Provider;

use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Webhook\Infrastructure\WebhookConfigProvider;

/**
 * Class DefaultWebhookConfigProvider.
 */
class DefaultWebhookConfigProvider implements WebhookConfigProvider
{
    /**
     * URI webhooks settings key.
     */
    const WEBHOOKS_URI_SETTING_KEY = 'uriWebhooks';

    /**
     * Enable webhooks settings key.
     */
    const WEBHOOKS_ENABLED_SETTING_KEY = 'webhooks';

    /**
     * @var SettingsManager
     */
    protected $settingsManager;

    /**
     * DefaultWebhookConfigProvider constructor.
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
    public function getUri(): string
    {
        $config = $this->settingsManager->getSettingByKey(self::WEBHOOKS_URI_SETTING_KEY);

        return $config ? $config->getValue() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        $config = $this->settingsManager->getSettingByKey(self::WEBHOOKS_ENABLED_SETTING_KEY);

        return $config ? $config->getValue() : false;
    }
}
