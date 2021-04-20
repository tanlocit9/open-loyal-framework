<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Command;

use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryId;

/**
 * Class CreateCampaignCategory.
 */
class CreateCampaignCategory extends CampaignCategoryCommand
{
    /**
     * @var array
     */
    protected $campaignCategoryData;

    /**
     * CreateCampaignCategory constructor.
     *
     * @param CampaignCategoryId $campaignCategoryId
     * @param array              $campaignCategoryData
     */
    public function __construct(CampaignCategoryId $campaignCategoryId, array $campaignCategoryData)
    {
        parent::__construct($campaignCategoryId);
        $this->campaignCategoryData = $campaignCategoryData;
    }

    /**
     * @return array
     */
    public function getCampaignCategoryData(): array
    {
        return $this->campaignCategoryData;
    }
}
