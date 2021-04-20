<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EmailSettingsBundle\Tests\Unit\Service;

use OpenLoyalty\Bundle\EmailSettingsBundle\Service\EmailSettings;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use Twig_Environment;
use Twig_Source;

/**
 * Class EmailSettingsTest.
 */
class EmailSettingsTest extends TestCase
{
    /**
     * @var EmailSettings
     */
    private $settingService;

    private const EMAIL_TEMPLATE = [
        [
            'template' => 'OpenLoyaltyUserBundle:email:registration.html.twig',
            'subject' => 'Account created',
            'variables' => [
                0 => 'url',
                1 => 'conditions_file',
            ],
        ],
        [
            'template' => 'OpenLoyaltyUserBundle:email:registration_with_temporary_password.html.twig',
            'subject' => 'Account created',
            'variables' => [
                0 => 'program_name',
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $twigSource = new Twig_Source('<html></html>', 'registration.html.twig');

        /** @var FilesystemLoader|MockObject $fileSystem */
        $fileSystem = $this->createMock(FilesystemLoader::class);
        $fileSystem->method('exists')->willReturn(true);
        $fileSystem->method('getSourceContext')->willReturn($twigSource);

        /** @var Twig_Environment|MockObject $twigEnvironment */
        $twigEnvironment = $this->createMock(Twig_Environment::class);
        $this->settingService = new EmailSettings(self::EMAIL_TEMPLATE, $fileSystem, $twigEnvironment);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_trying_to_get_a_non_existing_setting(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->settingService->getDefaultSetting('non_exists_setting');
    }

    /**
     * @test
     */
    public function it_returns_value_of_option_when_option_exists_by_key(): void
    {
        $this->settingService->addDefaultSettings('settingKey', 'test');
        $actual = $this->settingService->getDefaultSetting('settingKey');
        $this->assertSame('test', $actual);
    }

    /**
     * @test
     */
    public function it_overwrites_setting_value_when_setting_key_exists_in_settings_bag(): void
    {
        $this->settingService->addDefaultSettings('settingKey', 'val1');
        $actual = $this->settingService->getDefaultSetting('settingKey');
        $this->assertSame('val1', $actual);

        $this->settingService->addDefaultSettings('settingKey', 'val2');
        $actual = $this->settingService->getDefaultSetting('settingKey');
        $this->assertSame('val2', $actual);
    }

    /**
     * @test
     */
    public function it_returns_true_when_given_email_template_exists(): void
    {
        $actual = $this->settingService->templateExistsByName('registration');
        $this->assertTrue($actual);
    }

    /**
     * @test
     */
    public function it_fails_to_find_a_non_existing_email_template(): void
    {
        $actual = $this->settingService->templateExistsByName('non_exists_template');
        $this->assertFalse($actual);
    }

    /**
     * @test
     */
    public function it_returns_template_parameters_for_given_template_name(): void
    {
        $templateParameters = $this->settingService->filterByName('registration');
        $this->assertCount(5, $templateParameters);
    }

    /**
     * @test
     */
    public function it_does_not_return_template_parameters_for_non_existing_template(): void
    {
        $templateParameters = $this->settingService->filterByName('non_exists_template');
        $this->assertCount(0, $templateParameters);
    }
}
