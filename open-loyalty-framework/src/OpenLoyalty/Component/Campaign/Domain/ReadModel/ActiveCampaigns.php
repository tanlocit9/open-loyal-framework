<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\ReadModel;

/**
 * Class ActiveCampaigns.
 */
class ActiveCampaigns
{
    /**
     * @var CampaignShortInfo[]
     */
    private $campaigns;

    /**
     * ActiveCampaigns constructor.
     */
    public function __construct()
    {
        $this->campaigns = [];
    }

    /**
     * @param CampaignShortInfo $campaignShortInfo
     */
    public function addCampaign(CampaignShortInfo $campaignShortInfo): void
    {
        $this->campaigns[] = $campaignShortInfo;
    }

    /**
     * @return array
     */
    public function getCampaigns(): array
    {
        return $this->campaigns;
    }
}
