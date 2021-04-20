<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Model;

use OpenLoyalty\Component\Campaign\Domain\CampaignCategory as DomainCampaignCategory;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryTranslation;

/**
 * Class CampaignCategory.
 */
class CampaignCategory extends DomainCampaignCategory
{
    /**
     * Campaign constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $this->mergeNewTranslations();

        $translations = array_map(
            function (CampaignCategoryTranslation $category): array {
                return [
                    'name' => $category->getName(),
                ];
            },
            $this->getTranslations()->toArray()
        );

        return [
            'name' => $this->getName(),
            'sortOrder' => $this->getSortOrder(),
            'active' => $this->isActive(),
            'translations' => $translations,
        ];
    }
}
