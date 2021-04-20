<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Translation\Domain;

/**
 * Interface LanguageRepository.
 */
interface LanguageRepository
{
    /**
     * @param LanguageId $languageId
     *
     * @return Language|null
     */
    public function byId(LanguageId $languageId): Language;

    /**
     * @return array
     */
    public function findAll(): array;

    /**
     * @param string $code
     *
     * @return Language
     */
    public function byCode(string $code): ?Language;

    /**
     * @return Language
     */
    public function getDefault(): ?Language;

    /**
     * @param Language $language
     */
    public function save(Language $language): void;

    /**
     * @param Language $language
     */
    public function remove(Language $language): void;
}
