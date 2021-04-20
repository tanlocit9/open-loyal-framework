<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use OpenLoyalty\Component\Core\Domain\Model\LabelMultiplier;

/**
 * Class LabelMultipliersJsonArrayDoctrineType.
 */
class LabelMultipliersJsonArrayDoctrineType extends Type
{
    const NAME = 'label_multipliers_json_array';

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!is_array($value)) {
            return json_encode([]);
        }

        $serialized = [];
        /** @var LabelMultiplier $labelMultiplier */
        foreach ($value as $labelMultiplier) {
            $serialized[] = $labelMultiplier->serialize();
        }

        return json_encode($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            return [];
        }

        $labelMultipliers = [];
        foreach ($decoded as $item) {
            $labelMultipliers[] = LabelMultiplier::deserialize($item);
        }

        return $labelMultipliers;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::NAME;
    }
}
