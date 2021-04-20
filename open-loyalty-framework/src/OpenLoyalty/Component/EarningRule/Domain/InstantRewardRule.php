<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use Assert\Assertion as Assert;
use OpenLoyalty\Component\EarningRule\Domain\Stoppable\StoppableInterface;

class InstantRewardRule extends EarningRule implements StoppableInterface
{
    /**
     * @var CampaignId
     */
    private $rewardCampaignId;

    public function setFromArray(array $earningRuleData = [])
    {
        parent::setFromArray($earningRuleData);

        if (isset($earningRuleData['rewardCampaignId'])) {
            $this->rewardCampaignId = new CampaignId($earningRuleData['rewardCampaignId']);
        }
    }

    public static function validateRequiredData(array $earningRuleData = [])
    {
        parent::validateRequiredData($earningRuleData);
        Assert::keyIsset($earningRuleData, 'rewardCampaignId');
        Assert::notBlank($earningRuleData['rewardCampaignId']);
    }

    /**
     * @return null|CampaignId
     */
    public function getRewardCampaignId(): ?CampaignId
    {
        return $this->rewardCampaignId;
    }

    /**
     * @param CampaignId $rewardCampaignId
     */
    public function setRewardCampaignId(CampaignId $rewardCampaignId = null): void
    {
        $this->rewardCampaignId = $rewardCampaignId;
    }
}
