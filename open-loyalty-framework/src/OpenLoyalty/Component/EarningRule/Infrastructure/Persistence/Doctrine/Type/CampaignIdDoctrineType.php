<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use OpenLoyalty\Component\EarningRule\Domain\CampaignId;
use Ramsey\Uuid\Doctrine\UuidType;

/**
 * Class CampaignIdDoctrineType.
 */
class CampaignIdDoctrineType extends UuidType
{
    const NAME = 'campaign_id';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return;
        }

        if ($value instanceof CampaignId) {
            return $value;
        }

        return new CampaignId($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null == $value) {
            return;
        }

        if ($value instanceof CampaignId) {
            return $value->__toString();
        }

        return;
    }

    public function getName()
    {
        return self::NAME;
    }
}
