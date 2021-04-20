<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Command;

use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryId;

/**
 * Class CampaignCategoryCommand.
 */
abstract class CampaignCategoryCommand
{
    /**
     * @var CampaignCategoryId
     */
    protected $campaignCategoryId;

    /**
     * CampaignCategoryCommand constructor.
     *
     * @param CampaignCategoryId $campaignCategoryId
     */
    public function __construct(CampaignCategoryId $campaignCategoryId)
    {
        $this->campaignCategoryId = $campaignCategoryId;
    }

    /**
     * @return CampaignCategoryId
     */
    public function getCampaignCategoryId(): CampaignCategoryId
    {
        return $this->campaignCategoryId;
    }
}
