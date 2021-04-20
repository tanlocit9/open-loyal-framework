<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Model;

/**
 * Class CashbackSimulation.
 */
class CashbackSimulation
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
     * CashbackSimulation constructor.
     *
     * @param string $customerId
     * @param float  $pointsAmount
     * @param float  $pointValue
     * @param float  $rewardAmount
     */
    public function __construct($customerId, $pointsAmount, $pointValue, $rewardAmount)
    {
        $this->customerId = $customerId;
        $this->pointsAmount = $pointsAmount;
        $this->pointValue = $pointValue;
        $this->rewardAmount = $rewardAmount;
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return float
     */
    public function getPointsAmount()
    {
        return $this->pointsAmount;
    }

    /**
     * @return float
     */
    public function getPointValue()
    {
        return $this->pointValue;
    }

    /**
     * @return float
     */
    public function getRewardAmount()
    {
        return $this->rewardAmount;
    }
}
