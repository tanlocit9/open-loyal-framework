<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Tests\Unit\Service;

use Broadway\CommandHandling\CommandBus;
use Broadway\UuidGenerator\Testing\MockUuidGenerator;
use OpenLoyalty\Bundle\SettingsBundle\Model\TranslationsEntry;
use OpenLoyalty\Bundle\SettingsBundle\Service\DoctrineTranslationsManager;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Translation\Domain\Command\CreateLanguage;
use OpenLoyalty\Component\Translation\Domain\Command\RemoveLanguage;
use OpenLoyalty\Component\Translation\Domain\Command\UpdateLanguage;
use OpenLoyalty\Component\Translation\Domain\Language;
use OpenLoyalty\Component\Translation\Domain\LanguageId;
use OpenLoyalty\Component\Translation\Domain\LanguageRepository;
use PHPUnit\Framework\TestCase;

/**
 * Class DoctrineTranslationsManagerTest.
 */
class DoctrineTranslationsManagerTest extends TestCase
{
    /**
     * @test
     */
    public function it_updates_translations(): void
    {
        $settingsManagerMock = $this->getMockBuilder(SettingsManager::class)->getMock();
        $languageRepositoryMock = $this->getMockBuilder(LanguageRepository::class)->getMock();

        $language = new Language();
        $language->setLanguageId(new LanguageId('f99748f2-0000-0000-a4a6-cec0c9320000'));
        $language->setCode('en');
        $language->setDefault(true);
        $language->setOrder(0);
        $language->setName('english');
        $language->setTranslations(['key' => 'value']);
        $language->setUpdatedAt(new \DateTime());

        $languageRepositoryMock->method('byCode')->willReturn($language);

        $commandBusMock = $this->getMockBuilder(CommandBus::class)->getMock();
        $commandBusMock->expects($this->once())->method('dispatch')->with(
            $this->equalTo(new UpdateLanguage(
                $language->getLanguageId(),
                [
                    'name' => 'english',
                    'order' => 0,
                    'default' => true,
                    'translations' => '{"key":"value"}',
                ]
            ))
        );

        $translationsManager = new DoctrineTranslationsManager($settingsManagerMock,
            $languageRepositoryMock, $commandBusMock, new MockUuidGenerator('f99748f2-0000-0000-a4a6-cec0c932ce01'));

        $translationEntry = new TranslationsEntry();
        $translationEntry->setCode('en');
        $translationEntry->setName('english');
        $translationEntry->setDefault(true);
        $translationEntry->setContent('{"key":"value"}');

        $translationsManager->update($translationEntry);
    }

    /**
     * @test
     */
    public function it_creates_translations(): void
    {
        $settingsManagerMock = $this->getMockBuilder(SettingsManager::class)->getMock();
        $languageRepositoryMock = $this->getMockBuilder(LanguageRepository::class)->getMock();

        $languageRepositoryMock->method('byCode')->willReturn(null);

        $commandBusMock = $this->getMockBuilder(CommandBus::class)->getMock();
        $commandBusMock->expects($this->once())->method('dispatch')->with(
            $this->equalTo(new CreateLanguage(
                new LanguageId('f99748f2-0000-0000-a4a6-cec0c932ce01'),
                [
                    'name' => 'english',
                    'order' => 0,
                    'code' => 'en',
                    'default' => true,
                    'translations' => '{"key":"value"}',
                ]
            ))
        );

        $translationsManager = new DoctrineTranslationsManager($settingsManagerMock,
            $languageRepositoryMock, $commandBusMock, new MockUuidGenerator('f99748f2-0000-0000-a4a6-cec0c932ce01'));

        $translationEntry = new TranslationsEntry();
        $translationEntry->setCode('en');
        $translationEntry->setName('english');
        $translationEntry->setDefault(true);
        $translationEntry->setContent('{"key":"value"}');

        $translationsManager->create($translationEntry);
    }

    /**
     * @test
     */
    public function it_removes_translations(): void
    {
        $settingsManagerMock = $this->getMockBuilder(SettingsManager::class)->getMock();
        $languageRepositoryMock = $this->getMockBuilder(LanguageRepository::class)->getMock();

        $language = new Language();
        $language->setLanguageId(new LanguageId('f99748f2-0000-0000-a4a6-cec0c932ce01'));
        $language->setCode('en');
        $languageRepositoryMock->method('byCode')->willReturn($language);

        $commandBusMock = $this->getMockBuilder(CommandBus::class)->getMock();
        $commandBusMock->expects($this->once())->method('dispatch')->with(
            $this->equalTo(new RemoveLanguage(
                new LanguageId('f99748f2-0000-0000-a4a6-cec0c932ce01')
            ))
        );

        $translationsManager = new DoctrineTranslationsManager($settingsManagerMock,
            $languageRepositoryMock, $commandBusMock, new MockUuidGenerator('f99748f2-0000-0000-a4a6-cec0c932ce01'));

        $translationsManager->remove('en');
    }
}
