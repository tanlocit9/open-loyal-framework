<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use OpenLoyalty\Component\Segment\Domain\SegmentPartId;
use Ramsey\Uuid\Doctrine\UuidType;

/**
 * Class SegmentPartIdDoctrineType.
 */
class SegmentPartIdDoctrineType extends UuidType
{
    const NAME = 'segment_part_id';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return;
        }

        if ($value instanceof SegmentPartId) {
            return $value;
        }

        return new SegmentPartId($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null == $value) {
            return;
        }

        if ($value instanceof SegmentPartId) {
            return $value->__toString();
        }

        return;
    }

    public function getName()
    {
        return self::NAME;
    }
}
