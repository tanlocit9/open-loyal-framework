<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryId;
use Ramsey\Uuid\Doctrine\UuidType;

/**
 * Class CampaignCategoryIdDoctrineType.
 */
class CampaignCategoryIdDoctrineType extends UuidType
{
    /**
     * Type name.
     */
    const NAME = 'campaign_category_id';

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return;
        }

        if ($value instanceof CampaignCategoryId) {
            return $value;
        }

        return new CampaignCategoryId($value);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null == $value) {
            return;
        }

        if ($value instanceof CampaignCategoryId) {
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
