<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Tests\Unit\Service;

use OpenLoyalty\Bundle\SettingsBundle\Entity\StringSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Bundle\SettingsBundle\Service\TemplateProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class TemplateProviderTest.
 */
class TemplateProviderTest extends TestCase
{
    /**
     * @test
     */
    public function checks_if_parsed_css_is_valid()
    {
        $settingsManager = $this->getMockBuilder(SettingsManager::class)->getMock();

        $settingsManager->method('getSettingByKey')
            ->willReturnOnConsecutiveCalls(
                new StringSettingEntry('cssTemplate', '.test { color: {{ACCENT_COLOR}}; }'),
                new StringSettingEntry('accentColor', '#abcabc')
            );

        $templateProvider = new TemplateProvider($settingsManager);

        $this->assertSame('.test { color: #abcabc; }', $templateProvider->getCssContent());
    }

    /**
     * @test
     */
    public function checks_if_parsed_css_is_valid_for_default_values()
    {
        $settingsManager = $this->getMockBuilder(SettingsManager::class)->getMock();

        $settingsManager->method('getSettingByKey')
            ->willReturn(null);

        $templateProvider = new TemplateProvider($settingsManager);

        $this->assertSame('', $templateProvider->getCssContent());
    }
}
