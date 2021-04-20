<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Command;

use OpenLoyalty\Component\Campaign\Domain\CampaignId;

/**
 * Class UpdateCampaign.
 */
class UpdateCampaign extends CampaignCommand
{
    /**
     * @var array
     */
    protected $campaignData;

    public function __construct(CampaignId $campaignId, array $campaignData)
    {
        parent::__construct($campaignId);
        $this->campaignData = $campaignData;
    }

    /**
     * @return array
     */
    public function getCampaignData()
    {
        return $this->campaignData;
    }
}
