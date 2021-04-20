<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Translation\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use OpenLoyalty\Component\Translation\Domain\LanguageId;
use Ramsey\Uuid\Doctrine\UuidType;

/**
 * Class LanguageIdDoctrineType.
 */
final class LanguageIdDoctrineType extends UuidType
{
    /**
     * Name.
     */
    const NAME = 'language_id';

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return;
        }

        if ($value instanceof LanguageId) {
            return $value;
        }

        return new LanguageId($value);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null == $value) {
            return;
        }

        if ($value instanceof LanguageId) {
            return $value->__toString();
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
