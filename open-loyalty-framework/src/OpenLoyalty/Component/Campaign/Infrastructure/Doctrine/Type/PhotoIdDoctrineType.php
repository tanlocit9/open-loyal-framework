<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use OpenLoyalty\Component\Campaign\Domain\PhotoId;
use Ramsey\Uuid\Doctrine\UuidType;

/**
 * Class PhotoIdDoctrineType.
 */
class PhotoIdDoctrineType extends UuidType
{
    const NAME = 'photo_id';

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?PhotoId
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof PhotoId) {
            return $value;
        }

        return new PhotoId($value);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null == $value) {
            return null;
        }

        if ($value instanceof PhotoId) {
            return (string) $value;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }
}
