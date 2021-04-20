<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use OpenLoyalty\Component\EarningRule\Domain\Model\UsageSubject;
use Ramsey\Uuid\Doctrine\UuidType;

/**
 * Class EarningRuleUsageSubjectDoctrineType.
 */
class EarningRuleUsageSubjectDoctrineType extends UuidType
{
    const NAME = 'earning_rule_usage_subject';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return;
        }

        if ($value instanceof UsageSubject) {
            return $value;
        }

        return new UsageSubject($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null == $value) {
            return;
        }

        if ($value instanceof UsageSubject) {
            return $value->__toString();
        }

        return;
    }

    public function getName()
    {
        return self::NAME;
    }
}
