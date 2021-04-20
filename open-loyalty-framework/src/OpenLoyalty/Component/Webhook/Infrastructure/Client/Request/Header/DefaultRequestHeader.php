<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Webhook\Infrastructure\Client\Request\Header;

use OpenLoyalty\Bundle\SettingsBundle\Entity\StringSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;

/**
 * Class DefaultRequestHeader.
 */
final class DefaultRequestHeader implements RequestHeaderInterface
{
    public const USER_AGENT = 'OpenLoyalty';

    public const CONTENT_TYPE = 'application/json';

    /**
     * @var StringSettingEntry
     */
    private $headerName;

    /**
     * @var StringSettingEntry
     */
    private $headerValue;

    /**
     * DefaultRequestHeader constructor.
     *
     * @param SettingsManager $settingsManager
     */
    public function __construct(SettingsManager $settingsManager)
    {
        $this->headerName = $settingsManager->getSettingByKey('webhookHeaderName');
        $this->headerValue = $settingsManager->getSettingByKey('webhookHeaderValue');
    }

    /**
     * {@inheritdoc}
     */
    public function headers(): array
    {
        $requestHeaders = [
            'Content-Type' => self::CONTENT_TYPE,
            'User-Agent' => self::USER_AGENT,
        ];

        if (!empty($this->headerName->getValue())) {
            return array_merge($requestHeaders, [
                $this->headerName->getValue() => $this->headerValue->getValue(),
            ]);
        }

        return $requestHeaders;
    }
}
