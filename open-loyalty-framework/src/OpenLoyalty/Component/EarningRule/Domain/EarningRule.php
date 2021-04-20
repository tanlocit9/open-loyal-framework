<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use Assert\Assertion as Assert;
use Assert\AssertionFailedException;
use OpenLoyalty\Component\EarningRule\Domain\Model\EarningRulePhoto;

/**
 * Class EarningRule.
 */
abstract class EarningRule
{
    const TYPE_POINTS = 'points';
    const TYPE_EVENT = 'event';
    const TYPE_CUSTOM_EVENT = 'custom_event';
    const TYPE_PRODUCT_PURCHASE = 'product_purchase';
    const TYPE_MULTIPLY_FOR_PRODUCT = 'multiply_for_product';
    const TYPE_MULTIPLY_BY_PRODUCT_LABELS = 'multiply_by_product_labels';
    const TYPE_REFERRAL = 'referral';
    const TYPE_INSTANT_REWARD = 'instant_reward';
    const TYPE_GEOLOCATION = 'geolocation';
    const TYPE_QRCODE = 'qrcode';

    const TYPE_MAP = [
        self::TYPE_EVENT => EventEarningRule::class,
        self::TYPE_CUSTOM_EVENT => CustomEventEarningRule::class,
        self::TYPE_POINTS => PointsEarningRule::class,
        self::TYPE_PRODUCT_PURCHASE => ProductPurchaseEarningRule::class,
        self::TYPE_MULTIPLY_FOR_PRODUCT => MultiplyPointsForProductEarningRule::class,
        self::TYPE_MULTIPLY_BY_PRODUCT_LABELS => MultiplyPointsByProductLabelsEarningRule::class,
        self::TYPE_REFERRAL => ReferralEarningRule::class,
        self::TYPE_INSTANT_REWARD => InstantRewardRule::class,
        self::TYPE_GEOLOCATION => EarningRuleGeo::class,
        self::TYPE_QRCODE => EarningRuleQrcode::class,
    ];

    /**
     * @var EarningRuleId
     */
    protected $earningRuleId;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $description;

    /**
     * @var LevelId[]
     */
    protected $levels = [];

    /**
     * @var SegmentId[]
     */
    protected $segments = [];

    /**
     * @var PosId[]
     */
    protected $pos = [];

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var \DateTime
     */
    protected $startAt;

    /**
     * @var \DateTime
     */
    protected $endAt;

    /**
     * @var bool
     */
    protected $allTimeActive = false;

    /**
     * @var EarningRuleUsage[]
     */
    protected $usages;

    /**
     * @var EarningRulePhoto
     */
    protected $earningRulePhoto;

    /**
     * @var bool
     */
    protected $lastExecutedRule = false;

    /**
     * EarningRule constructor.
     *
     * @param EarningRuleId $earningRuleId
     * @param array         $earningRuleData
     */
    public function __construct(EarningRuleId $earningRuleId, array $earningRuleData = [])
    {
        $this->earningRuleId = $earningRuleId;
        $this->setFromArray($earningRuleData);
    }

    /**
     * @param array $earningRuleData
     */
    public function setFromArray(array $earningRuleData = [])
    {
        if (isset($earningRuleData['name'])) {
            $this->name = $earningRuleData['name'];
        }
        if (isset($earningRuleData['description'])) {
            $this->description = $earningRuleData['description'];
        }
        if (isset($earningRuleData['levels'])) {
            $this->levels = $earningRuleData['levels'];
        }
        if (isset($earningRuleData['segments'])) {
            $this->segments = $earningRuleData['segments'];
        }
        if (isset($earningRuleData['pos'])) {
            $this->pos = $earningRuleData['pos'];
        }
        if (isset($earningRuleData['active'])) {
            $this->active = $earningRuleData['active'];
        }
        if (isset($earningRuleData['allTimeActive'])) {
            $this->allTimeActive = $earningRuleData['allTimeActive'];
        }
        if (isset($earningRuleData['startAt'])) {
            $tmp = new \DateTime();
            $tmp->setTimestamp($earningRuleData['startAt']);
            $this->startAt = $tmp;
        }
        if (isset($earningRuleData['endAt'])) {
            $tmp = new \DateTime();
            $tmp->setTimestamp($earningRuleData['endAt']);
            $this->endAt = $tmp;
        }
        if (isset($earningRuleData['lastExecutedRule'])) {
            $this->lastExecutedRule = $earningRuleData['lastExecutedRule'];
        }
    }

    /**
     * @return EarningRuleId
     */
    public function getEarningRuleId()
    {
        return $this->earningRuleId;
    }

    /**
     * @param EarningRuleId $earningRuleId
     */
    public function setEarningRuleId($earningRuleId)
    {
        $this->earningRuleId = $earningRuleId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return LevelId[]
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * @param LevelId[] $levels
     */
    public function setLevels($levels)
    {
        $this->levels = $levels;
    }

    /**
     * @return SegmentId[]
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @param SegmentId[] $segments
     */
    public function setSegments($segments)
    {
        $this->segments = $segments;
    }

    /**
     * @return PosId[]
     */
    public function getPos(): array
    {
        return $this->pos;
    }

    /**
     * @param PosId[] $pos
     */
    public function setPos(array $pos)
    {
        $this->pos = $pos;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return \DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * @param \DateTime $startAt
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;
    }

    /**
     * @return \DateTime
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * @param \DateTime $endAt
     */
    public function setEndAt($endAt)
    {
        $this->endAt = $endAt;
    }

    /**
     * @return bool
     */
    public function isAllTimeActive()
    {
        return $this->allTimeActive;
    }

    /**
     * @param bool $allTimeActive
     */
    public function setAllTimeActive($allTimeActive)
    {
        $this->allTimeActive = $allTimeActive;
    }

    /**
     * @param array $earningRuleData
     *
     * @throws AssertionFailedException
     */
    public static function validateRequiredData(array $earningRuleData = [])
    {
        Assert::keyIsset($earningRuleData, 'name');
        Assert::keyIsset($earningRuleData, 'description');
        if (!isset($earningRuleData['allTimeActive']) || !$earningRuleData['allTimeActive']) {
            Assert::keyIsset($earningRuleData, 'startAt');
            Assert::keyIsset($earningRuleData, 'endAt');
            Assert::notBlank($earningRuleData['startAt']);
            Assert::notBlank($earningRuleData['endAt']);
        }

        Assert::notBlank($earningRuleData['name']);
        Assert::notBlank($earningRuleData['description']);
    }

    public function addUsage(EarningRuleUsage $usage)
    {
        $usage->setEarningRule($this);
        $this->usages[] = $usage;
    }

    /**
     * @return EarningRuleUsage[]
     */
    public function getUsages()
    {
        return $this->usages;
    }

    /**
     * @param EarningRuleUsage[] $usages
     */
    public function setUsages($usages)
    {
        $this->usages = $usages;
    }

    public function getFlatLevels()
    {
        return array_map(function (LevelId $levelId) {
            return $levelId->__toString();
        }, $this->levels);
    }

    public function getFlatSegments()
    {
        return array_map(function (SegmentId $segmentId) {
            return $segmentId->__toString();
        }, $this->segments);
    }

    /**
     * @return array
     */
    public function getFlatPos(): array
    {
        return array_map(function (PosId $posId) {
            return $posId->__toString();
        }, $this->pos);
    }

    /**
     * @return EarningRulePhoto
     */
    public function getEarningRulePhoto(): EarningRulePhoto
    {
        return $this->earningRulePhoto;
    }

    /**
     * @param EarningRulePhoto $earningRulePhoto
     */
    public function setEarningRulePhoto(EarningRulePhoto $earningRulePhoto)
    {
        $this->earningRulePhoto = $earningRulePhoto;
    }

    /**
     * Removes Earning Rule Photo.
     */
    public function removeEarningRulePhoto()
    {
        $this->earningRulePhoto = null;
    }

    /**
     * @return bool
     */
    public function hasEarningRulePhoto(): bool
    {
        return $this->earningRulePhoto instanceof EarningRulePhoto && $this->earningRulePhoto->getPath();
    }

    /**
     * @return bool
     */
    public function isLastExecutedRule(): bool
    {
        return $this->lastExecutedRule;
    }

    /**
     * @param bool $lastExecutedRule
     */
    public function setLastExecutedRule(bool $lastExecutedRule): void
    {
        $this->lastExecutedRule = $lastExecutedRule;
    }
}
