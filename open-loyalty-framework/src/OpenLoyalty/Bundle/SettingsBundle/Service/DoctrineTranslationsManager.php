<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Service;

use Broadway\CommandHandling\CommandBus;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Bundle\SettingsBundle\Exception\AlreadyExistException;
use OpenLoyalty\Bundle\SettingsBundle\Exception\NotExistException;
use OpenLoyalty\Bundle\SettingsBundle\Model\TranslationsEntry;
use OpenLoyalty\Component\Translation\Domain\Command\CreateLanguage;
use OpenLoyalty\Component\Translation\Domain\Command\RemoveLanguage;
use OpenLoyalty\Component\Translation\Domain\Command\UpdateLanguage;
use OpenLoyalty\Component\Translation\Domain\Language;
use OpenLoyalty\Component\Translation\Domain\LanguageId;
use OpenLoyalty\Component\Translation\Domain\LanguageRepository;

/**
 * Class DoctrineTranslationsManager.
 */
class DoctrineTranslationsManager implements TranslationsProvider
{
    /**
     * @var SettingsManager
     */
    protected $settingsManager;

    /**
     * @var LanguageRepository
     */
    protected $languageRepository;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * DoctrineTranslationsManager constructor.
     *
     * @param SettingsManager        $settingsManager
     * @param LanguageRepository     $languageRepository
     * @param CommandBus             $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(SettingsManager $settingsManager, LanguageRepository $languageRepository, CommandBus $commandBus, UuidGeneratorInterface $uuidGenerator)
    {
        $this->settingsManager = $settingsManager;
        $this->languageRepository = $languageRepository;
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentTranslations(): TranslationsEntry
    {
        $language = $this->languageRepository->getDefault();

        if (!$language) {
            return new TranslationsEntry(
                null,
                null,
                '{}'
            );
        }

        $content = $language->getTranslations();

        return new TranslationsEntry(
            $language->getCode(),
            $language->getName(),
            json_encode($content),
            $language->getUpdatedAt()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationsByKey(string $code): TranslationsEntry
    {
        $language = $this->languageRepository->byCode($code);

        if (!$language) {
            throw new NotExistException();
        }

        return new TranslationsEntry(
            $language->getCode(),
            $language->getName(),
            json_encode($language->getTranslations()),
            $language->getUpdatedAt(),
            $language->getOrder(),
            $language->isDefault()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableTranslationsList(): array
    {
        /** @var Language[] $languages */
        $languages = $this->languageRepository->findAll();
        $translations = [];

        foreach ($languages as $language) {
            $translations[] = new TranslationsEntry(
                $language->getCode(),
                $language->getName(),
                null,
                $language->getUpdatedAt(),
                $language->getOrder(),
                $language->isDefault()
            );
        }

        return $translations;
    }

    /**
     * {@inheritdoc}
     */
    public function hasTranslation(string $code): bool
    {
        $language = $this->languageRepository->byCode($code);

        return !is_null($language);
    }

    /**
     * {@inheritdoc}
     */
    public function update(TranslationsEntry $entry): void
    {
        $language = $this->languageRepository->byCode($entry->getCode());

        if (!$language) {
            throw new NotExistException();
        }

        $this->commandBus->dispatch(new UpdateLanguage(
            $language->getLanguageId(),
            [
                'name' => $entry->getName(),
                'order' => $entry->getOrder(),
                'default' => $entry->isDefault(),
                'translations' => $entry->getContent(),
            ]
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function create(TranslationsEntry $entry): void
    {
        if ($this->languageRepository->byCode($entry->getCode())) {
            throw new AlreadyExistException();
        }

        $this->commandBus->dispatch(new CreateLanguage(
            new LanguageId($this->uuidGenerator->generate()),
            [
                'code' => $entry->getCode(),
                'name' => $entry->getName(),
                'order' => $entry->getOrder(),
                'default' => $entry->isDefault(),
                'translations' => $entry->getContent(),
            ]
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $code): void
    {
        $language = $this->languageRepository->byCode($code);

        if (!$language) {
            throw new NotExistException();
        }

        $this->commandBus->dispatch(new RemoveLanguage(
            new LanguageId($language->getLanguageId())
        ));
    }
}
