<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TranslationBundle\Model;

use Knp\DoctrineBehaviors\Model\Translatable\TranslationMethods;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationProperties;

/**
 * Trait FallbackTranslation.
 */
trait FallbackTranslation
{
    use TranslationProperties,
        TranslationMethods;

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public function isFieldEmpty(string $fieldName): bool
    {
        $fields = get_object_vars($this);

        return !array_key_exists($fieldName, $fields) || empty($fields[$fieldName]);
    }
}
