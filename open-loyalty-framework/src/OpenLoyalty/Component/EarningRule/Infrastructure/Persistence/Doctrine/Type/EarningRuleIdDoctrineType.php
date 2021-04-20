<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use Ramsey\Uuid\Doctrine\UuidType;

/**
 * Class EarningRuleIdDoctrineType.
 */
class EarningRuleIdDoctrineType extends UuidType
{
    const NAME = 'earning_rule_id';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return;
        }

        if ($value instanceof EarningRuleId) {
            return $value;
        }

        return new EarningRuleId($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null == $value) {
            return;
        }

        if ($value instanceof EarningRuleId) {
            return $value->__toString();
        }

        return;
    }

    public function getName()
    {
        return self::NAME;
    }
}
