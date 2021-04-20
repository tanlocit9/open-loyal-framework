<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Service;

use OpenLoyalty\Bundle\SettingsBundle\Exception\AlreadyExistException;
use OpenLoyalty\Bundle\SettingsBundle\Exception\NotExistException;
use OpenLoyalty\Bundle\SettingsBundle\Model\TranslationsEntry;

/**
 * Interface TranslationsProvider.
 */
interface TranslationsProvider
{
    /**
     * @return TranslationsEntry
     */
    public function getCurrentTranslations(): TranslationsEntry;

    /**
     * @param string $code
     *
     * @return TranslationsEntry
     */
    public function getTranslationsByKey(string $code): TranslationsEntry;

    /**
     * @return TranslationsEntry[]
     */
    public function getAvailableTranslationsList(): array;

    /**
     * @param string $code
     *
     * @return bool
     */
    public function hasTranslation(string $code): bool;

    /**
     * @param TranslationsEntry $entry
     *
     * @throws AlreadyExistException
     * @throws NotExistException
     */
    public function update(TranslationsEntry $entry): void;

    /**
     * @param TranslationsEntry $entry
     *
     * @throws AlreadyExistException
     */
    public function create(TranslationsEntry $entry): void;

    /**
     * @param string $code
     */
    public function remove(string $code): void;
}
