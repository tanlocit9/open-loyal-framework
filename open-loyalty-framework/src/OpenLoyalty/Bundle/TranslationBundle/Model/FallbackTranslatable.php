<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TranslationBundle\Model;

use Knp\DoctrineBehaviors\Model\Translatable\TranslatableMethods;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableProperties;

/**
 * Trait FallbackTranslatable.
 */
trait FallbackTranslatable
{
    use TranslatableProperties,
        TranslatableMethods;

    /**
     * @param null|string $locale
     * @param null|string $fieldName
     *
     * @return mixed
     */
    public function translateFieldFallback(?string $locale = null, ?string $fieldName = null)
    {
        if (null === $locale) {
            $locale = $this->getCurrentLocale();
        }

        $translation = $this->findTranslationByLocale($locale);
        if ($translation and !$translation->isFieldEmpty($fieldName)) {
            return $translation;
        }

        if (($fallbackLocale = $this->computeFallbackLocale($locale))
            && ($translation = $this->findTranslationByLocale($fallbackLocale))) {
            return $translation;
        }

        if ($defaultTranslation = $this->findTranslationByLocale($this->getDefaultLocale(), false)) {
            return $defaultTranslation;
        }

        $class = static::getTranslationEntityClass();
        $translation = new $class();
        $translation->setLocale($locale);

        $this->getNewTranslations()->set((string) $translation->getLocale(), $translation);
        $translation->setTranslatable($this);

        return $translation;
    }
}
