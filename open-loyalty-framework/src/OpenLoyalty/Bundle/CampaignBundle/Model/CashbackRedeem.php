<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Model;

/**
 * Class CashbackRedeem.
 */
class CashbackRedeem
{
    /**
     * @var string
     */
    private $customerId;

    /**
     * @var float
     */
    private $pointsAmount;

    /**
     * @var float
     */
    private $pointValue;

    /**
     * @var float
     */
    private $rewardAmount;

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return float
     */
    public function getPointsAmount()
    {
        return $this->pointsAmount;
    }

    /**
     * @param float $pointsAmount
     */
    public function setPointsAmount($pointsAmount)
    {
        $this->pointsAmount = $pointsAmount;
    }

    /**
     * @return float
     */
    public function getPointValue()
    {
        return $this->pointValue;
    }

    /**
     * @param float $pointValue
     */
    public function setPointValue($pointValue)
    {
        $this->pointValue = $pointValue;
    }

    /**
     * @return float
     */
    public function getRewardAmount()
    {
        return $this->rewardAmount;
    }

    /**
     * @param float $rewardAmount
     */
    public function setRewardAmount($rewardAmount)
    {
        $this->rewardAmount = $rewardAmount;
    }
}
