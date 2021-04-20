<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryId;

/**
 * Class CampaignCategoriesJsonArrayDoctrineType.
 */
class CampaignCategoriesJsonArrayDoctrineType extends Type
{
    /**
     * Type name.
     */
    const NAME = 'campaign_categories_json_array';

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

        /** @var CampaignCategoryId $campaignCategoryId */
        foreach ($value as $campaignCategoryId) {
            $serialized[] = $campaignCategoryId->__toString();
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

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;
        $decoded = json_decode($value, true);

        if (!$decoded) {
            return [];
        }

        $result = [];

        foreach ($decoded as $decodedItem) {
            $result[] = new CampaignCategoryId($decodedItem);
        }

        return $result;
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
