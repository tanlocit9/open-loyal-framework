<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Command;

use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Model\CampaignBrandIcon;
use OpenLoyalty\Component\Campaign\Domain\Model\CampaignFile;

/**
 * Class SetCampaignBrandIcon.
 */
class SetCampaignBrandIcon extends CampaignCommand
{
    /**
     * @var CampaignBrandIcon
     */
    protected $campaignBrandIcon;

    /**
     * SetCampaignBrandIcon constructor.
     *
     * @param CampaignId   $campaignId
     * @param CampaignFile $campaignFile
     */
    public function __construct(CampaignId $campaignId, CampaignFile $campaignFile)
    {
        parent::__construct($campaignId);
        $this->campaignBrandIcon = $campaignFile;
    }

    /**
     * @return CampaignBrandIcon
     */
    public function getCampaignBrandIcon(): CampaignBrandIcon
    {
        return $this->campaignBrandIcon;
    }
}
