<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Translation\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use OpenLoyalty\Component\Translation\Domain\Language;
use OpenLoyalty\Component\Translation\Domain\LanguageRepository;

/**
 * Class LanguageCommandHandler.
 */
class LanguageCommandHandler extends SimpleCommandHandler
{
    /** @var LanguageRepository */
    protected $languageRepository;

    /**
     * LanguageCommandHandler constructor.
     *
     * @param LanguageRepository $languageRepository
     */
    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @param UpdateLanguage $command
     */
    public function handleUpdateLanguage(UpdateLanguage $command)
    {
        $language = $this->languageRepository->byId($command->getLanguageId());

        if (!$language) {
            throw new \InvalidArgumentException('languageId');
        }

        $data = $command->getLanguageData();

        $language->setName($data['name']);
        $language->setUpdatedAt(new \DateTime());
        $language->setTranslations(json_decode($data['translations'], true));
        $language->setOrder($data['order']);
        $language->setDefault($data['default']);

        $this->languageRepository->save($language);
    }

    /**
     * @param CreateLanguage $command
     */
    public function handleCreateLanguage(CreateLanguage $command)
    {
        $data = $command->getLanguageData();

        $language = new Language();
        $language->setLanguageId($command->getLanguageId());
        $language->setCode($data['code']);
        $language->setName($data['name']);
        $language->setUpdatedAt(new \DateTime());
        $language->setTranslations(json_decode($data['translations'], true));
        $language->setOrder($data['order']);
        $language->setDefault($data['default']);

        $this->languageRepository->save($language);
    }

    /**
     * @param RemoveLanguage $command
     */
    public function handleRemoveLanguage(RemoveLanguage $command)
    {
        $language = $this->languageRepository->byId($command->getLanguageId());

        if (!$language) {
            throw new \InvalidArgumentException('languageId');
        }

        $this->languageRepository->remove($language);
    }
}
