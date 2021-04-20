<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Service;

/**
 * Class TemplateProvider.
 */
class TemplateProvider
{
    const DEFAULT_ACCENT_COLOR = 'black';
    const DEFAULT_CSS_TEMPLATE = '';

    /**
     * @var SettingsManager
     */
    protected $settingsManager;

    /**
     * TemplateProvider constructor.
     *
     * @param SettingsManager $settingsManager
     */
    public function __construct(SettingsManager $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    /**
     * @return array
     */
    public function getJsonContent(): array
    {
        $accentColorSetting = $this->settingsManager->getSettingByKey('accentColor');

        return [
            'accent_color' => (!empty($accentColorSetting)) ? $accentColorSetting->getValue() : '',
            'template_css' => $this->getCssContent(),
        ];
    }

    /**
     * @return string
     */
    public function getCssContent()
    {
        $cssTemplateSetting = $this->settingsManager->getSettingByKey('cssTemplate');
        $accentColorSetting = $this->settingsManager->getSettingByKey('accentColor');

        $patterns = [
            '/{{ACCENT_COLOR}}/',
        ];

        $replacements = [
            $accentColorSetting
                ? $accentColorSetting->getValue()
                : self::DEFAULT_ACCENT_COLOR,
        ];

        return preg_replace(
            $patterns,
            $replacements,
            $cssTemplateSetting ? $cssTemplateSetting->getValue() : self::DEFAULT_CSS_TEMPLATE
        );
    }
}
