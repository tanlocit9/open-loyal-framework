<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Model;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use JMS\Serializer\Annotation as JMS;

/**
 * Class CustomerStatus.
 */
class CustomerStatus
{
    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var CustomerId
     * @JMS\Inline()
     */
    protected $customerId;

    /**
     * @var float
     */
    protected $points = 0;

    /**
     * @var float
     */
    protected $p2pPoints = 0;

    /**
     * @var float
     */
    protected $totalEarnedPoints = 0;

    /**
     * @var float
     */
    protected $usedPoints = 0;

    /**
     * @var float
     */
    protected $expiredPoints = 0;

    /**
     * @var float
     */
    protected $lockedPoints = 0;

    /**
     * @var string
     * @JMS\SerializedName("level")
     */
    protected $levelPercent;

    /**
     * @var string
     */
    protected $levelName;

    /**
     * @var float
     */
    protected $levelConditionValue = 0.00;

    /**
     * @var string
     * @JMS\SerializedName("nextLevel")
     */
    protected $nextLevelPercent;

    /**
     * @var string
     */
    protected $nextLevelName;

    /**
     * @var float
     */
    protected $nextLevelConditionValue = 0.00;

    /**
     * @var float
     */
    protected $transactionsAmountToNextLevelWithoutDeliveryCosts;

    /**
     * @var float
     */
    protected $transactionsAmountWithoutDeliveryCosts;

    /**
     * @var float
     */
    protected $transactionsAmountToNextLevel;

    /**
     * @var float
     */
    protected $averageTransactionsAmount;

    /**
     * @var int
     */
    protected $transactionsCount;

    /**
     * @var float
     */
    protected $transactionsAmount;

    /**
     * @var float
     */
    protected $pointsToNextLevel;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var int|null
     */
    protected $levelWillExpireInDays;

    /**
     * @var float|null
     */
    protected $pointsSinceLastLevelRecalculation;

    /**
     * @var float|null
     */
    protected $pointsRequiredToRetainLevel;

    /**
     * @var int|null
     */
    private $pointsExpiringNextMonth;

    /**
     * @var array
     */
    private $pointsExpiringBreakdown;

    /**
     * CustomerStatus constructor.
     *
     * @param CustomerId $customerId
     */
    public function __construct(CustomerId $customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param CustomerId $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return float
     */
    public function getPoints(): float
    {
        return $this->points;
    }

    /**
     * @param float $points
     */
    public function setPoints(float $points)
    {
        $this->points = $points;
    }

    /**
     * @param float $totalEarnedPoints
     */
    public function setTotalEarnedPoints(float $totalEarnedPoints)
    {
        $this->totalEarnedPoints = $totalEarnedPoints;
    }

    /**
     * @return string
     */
    public function getLevelPercent()
    {
        return $this->levelPercent;
    }

    /**
     * @param string $levelPercent
     */
    public function setLevelPercent($levelPercent)
    {
        $this->levelPercent = $levelPercent;
    }

    /**
     * @return string
     */
    public function getNextLevelPercent()
    {
        return $this->nextLevelPercent;
    }

    /**
     * @param string $nextLevelPercent
     */
    public function setNextLevelPercent($nextLevelPercent)
    {
        $this->nextLevelPercent = $nextLevelPercent;
    }

    /**
     * @return float
     */
    public function getTransactionsAmountToNextLevelWithoutDeliveryCosts()
    {
        return $this->transactionsAmountToNextLevelWithoutDeliveryCosts;
    }

    /**
     * @param float $transactionsAmountToNextLevelWithoutDeliveryCosts
     */
    public function setTransactionsAmountToNextLevelWithoutDeliveryCosts(
        $transactionsAmountToNextLevelWithoutDeliveryCosts
    ) {
        $this->transactionsAmountToNextLevelWithoutDeliveryCosts = $transactionsAmountToNextLevelWithoutDeliveryCosts;
    }

    /**
     * @return float
     */
    public function getTransactionsAmountWithoutDeliveryCosts()
    {
        return $this->transactionsAmountWithoutDeliveryCosts;
    }

    /**
     * @param float $transactionsAmountWithoutDeliveryCosts
     */
    public function setTransactionsAmountWithoutDeliveryCosts($transactionsAmountWithoutDeliveryCosts)
    {
        $this->transactionsAmountWithoutDeliveryCosts = $transactionsAmountWithoutDeliveryCosts;
    }

    /**
     * @return float
     */
    public function getTransactionsAmountToNextLevel()
    {
        return $this->transactionsAmountToNextLevel;
    }

    /**
     * @param float $transactionsAmountToNextLevel
     */
    public function setTransactionsAmountToNextLevel($transactionsAmountToNextLevel)
    {
        $this->transactionsAmountToNextLevel = $transactionsAmountToNextLevel;
    }

    /**
     * @return float
     */
    public function getTransactionsAmount()
    {
        return $this->transactionsAmount;
    }

    /**
     * @param float $transactionsAmount
     */
    public function setTransactionsAmount($transactionsAmount)
    {
        $this->transactionsAmount = $transactionsAmount;
    }

    /**
     * @return float
     */
    public function getPointsToNextLevel(): float
    {
        return $this->pointsToNextLevel;
    }

    /**
     * @param float $pointsToNextLevel
     */
    public function setPointsToNextLevel(float $pointsToNextLevel)
    {
        $this->pointsToNextLevel = $pointsToNextLevel;
    }

    /**
     * @return float
     */
    public function getUsedPoints(): float
    {
        return $this->usedPoints;
    }

    /**
     * @param float $usedPoints
     */
    public function setUsedPoints(float $usedPoints)
    {
        $this->usedPoints = $usedPoints;
    }

    /**
     * @return float
     */
    public function getExpiredPoints(): float
    {
        return $this->expiredPoints;
    }

    /**
     * @param float $expiredPoints
     */
    public function setExpiredPoints(float $expiredPoints)
    {
        $this->expiredPoints = $expiredPoints;
    }

    /**
     * @param float $lockedPoints
     */
    public function setLockedPoints(float $lockedPoints)
    {
        $this->lockedPoints = $lockedPoints;
    }

    /**
     * @return string
     */
    public function getLevelName()
    {
        return $this->levelName;
    }

    /**
     * @param string $levelName
     */
    public function setLevelName($levelName)
    {
        $this->levelName = $levelName;
    }

    /**
     * @return string
     */
    public function getNextLevelName()
    {
        return $this->nextLevelName;
    }

    /**
     * @param string $nextLevelName
     */
    public function setNextLevelName($nextLevelName)
    {
        $this->nextLevelName = $nextLevelName;
    }

    /**
     * @return float
     */
    public function getAverageTransactionsAmount()
    {
        return $this->averageTransactionsAmount;
    }

    /**
     * @param float $averageTransactionsAmount
     */
    public function setAverageTransactionsAmount($averageTransactionsAmount)
    {
        $this->averageTransactionsAmount = $averageTransactionsAmount;
    }

    /**
     * @return int
     */
    public function getTransactionsCount()
    {
        return $this->transactionsCount;
    }

    /**
     * @param int $transactionsCount
     */
    public function setTransactionsCount($transactionsCount)
    {
        $this->transactionsCount = $transactionsCount;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return int|null
     */
    public function getLevelWillExpireInDays(): ?int
    {
        return $this->levelWillExpireInDays;
    }

    /**
     * @param int|null $levelWillExpireInDays
     */
    public function setLevelWillExpireInDays(?int $levelWillExpireInDays): void
    {
        $this->levelWillExpireInDays = $levelWillExpireInDays;
    }

    /**
     * @return float|null
     */
    public function getPointsSinceLastLevelRecalculation(): ?float
    {
        return $this->pointsSinceLastLevelRecalculation;
    }

    /**
     * @param float|null $pointsSinceLastLevelRecalculation
     */
    public function setPointsSinceLastLevelRecalculation(?float $pointsSinceLastLevelRecalculation): void
    {
        $this->pointsSinceLastLevelRecalculation = $pointsSinceLastLevelRecalculation;
    }

    /**
     * @return float
     */
    public function getP2pPoints(): float
    {
        return $this->p2pPoints;
    }

    /**
     * @param float $p2pPoints
     */
    public function setP2pPoints(float $p2pPoints): void
    {
        $this->p2pPoints = $p2pPoints;
    }

    /**
     * @return float|null
     */
    public function getPointsRequiredToRetainLevel(): ?float
    {
        return $this->pointsRequiredToRetainLevel;
    }

    /**
     * @param float|null $pointsRequiredToRetainLevel
     */
    public function setPointsRequiredToRetainLevel(?float $pointsRequiredToRetainLevel): void
    {
        $this->pointsRequiredToRetainLevel = $pointsRequiredToRetainLevel;
    }

    /**
     * @return float
     */
    public function getLevelConditionValue(): float
    {
        return $this->levelConditionValue;
    }

    /**
     * @param float $levelConditionValue
     */
    public function setLevelConditionValue(float $levelConditionValue): void
    {
        $this->levelConditionValue = $levelConditionValue;
    }

    /**
     * @return float
     */
    public function getNextLevelConditionValue(): float
    {
        return $this->nextLevelConditionValue;
    }

    /**
     * @param float $nextLevelConditionValue
     */
    public function setNextLevelConditionValue(float $nextLevelConditionValue): void
    {
        $this->nextLevelConditionValue = $nextLevelConditionValue;
    }

    /**
     * @return int|null
     */
    public function getPointsExpiringNextMonth(): ?int
    {
        return $this->pointsExpiringNextMonth;
    }

    /**
     * @param float|null $pointsExpiringNextMonth
     */
    public function setPointsExpiringNextMonth(?float $pointsExpiringNextMonth): void
    {
        $this->pointsExpiringNextMonth = $pointsExpiringNextMonth;
    }

    /**
     * @return array
     */
    public function getPointsExpiringBreakdown(): ?array
    {
        return $this->pointsExpiringBreakdown;
    }

    /**
     * @param array|null $pointsExpiringNextMonthBrakedown
     */
    public function setPointsExpiringBreakdown(?array $pointsExpiringBrakedown): void
    {
        $this->pointsExpiringBreakdown = $pointsExpiringBrakedown;
    }
}
