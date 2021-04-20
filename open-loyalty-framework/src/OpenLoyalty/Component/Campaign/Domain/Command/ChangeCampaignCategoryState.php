<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Command;

use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryId;

/**
 * Class ChangeCampaignCategoryState.
 */
class ChangeCampaignCategoryState extends CampaignCategoryCommand
{
    /**
     * @var bool
     */
    protected $active;

    /**
     * ChangeCampaignCategoryState constructor.
     *
     * @param CampaignCategoryId $campaignCategoryId
     * @param bool               $active
     */
    public function __construct(CampaignCategoryId $campaignCategoryId, bool $active)
    {
        parent::__construct($campaignCategoryId);
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getActive(): bool
    {
        return $this->active;
    }
}
