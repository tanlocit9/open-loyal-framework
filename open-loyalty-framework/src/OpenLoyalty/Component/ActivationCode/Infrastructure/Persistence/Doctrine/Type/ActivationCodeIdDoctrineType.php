<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\ActivationCode\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use OpenLoyalty\Component\ActivationCode\Domain\ActivationCodeId;
use Ramsey\Uuid\Doctrine\UuidType;

/**
 * Class ActivationCodeIdDoctrineType.
 */
class ActivationCodeIdDoctrineType extends UuidType
{
    const NAME = 'activation_code_id';

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return;
        }

        if ($value instanceof ActivationCodeId) {
            return $value;
        }

        return new ActivationCodeId($value);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof ActivationCodeId) {
            return $value->__toString();
        }

        if (!empty($value)) {
            return $value;
        }

        return;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
