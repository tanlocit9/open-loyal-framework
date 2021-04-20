<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Level\Domain;

use OpenLoyalty\Bundle\TranslationBundle\Model\FallbackTranslatable;
use OpenLoyalty\Component\Level\Domain\Model\LevelPhoto;
use OpenLoyalty\Component\Level\Domain\Model\Reward;
use Assert\Assertion as Assert;

/**
 * Class Level.
 */
class Level
{
    use FallbackTranslatable;

    /**
     * @var LevelId
     */
    protected $levelId;

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var float
     */
    protected $conditionValue;

    /**
     * @var Reward
     */
    protected $reward;

    /**
     * @var SpecialReward[]
     */
    protected $specialRewards = [];

    /**
     * @var int
     */
    protected $customersCount = 0;

    /**
     * @var float
     */
    protected $minOrder;

    /**
     * @var LevelPhoto
     */
    protected $photo;

    /**
     * Level constructor.
     *
     * @param LevelId $levelId
     * @param $conditionValue
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(LevelId $levelId, $conditionValue)
    {
        Assert::notEmpty($levelId);
        Assert::greaterOrEqualThan($conditionValue, 0);

        $this->levelId = $levelId;
        $this->conditionValue = $conditionValue;
    }

    /**
     * @return LevelId
     */
    public function getLevelId()
    {
        return $this->levelId;
    }

    /**
     * @return string
     */
    public function getIdAsString(): string
    {
        return (string) $this->levelId;
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
     * @return Reward
     */
    public function getReward()
    {
        return $this->reward;
    }

    /**
     * @param Reward $reward
     */
    public function setReward($reward)
    {
        Assert::notEmpty($reward);
        $this->reward = $reward;
    }

    /**
     * @return array
     */
    public function getSpecialRewards()
    {
        return $this->specialRewards;
    }

    /**
     * @param array $specialRewards
     */
    public function setSpecialRewards($specialRewards)
    {
        $this->specialRewards = $specialRewards;
    }

    public function addSpecialReward(SpecialReward $specialReward)
    {
        $this->specialRewards[] = $specialReward;
    }

    /**
     * @return float
     */
    public function getConditionValue()
    {
        return $this->conditionValue;
    }

    /**
     * @param float $conditionValue
     */
    public function setConditionValue($conditionValue)
    {
        $this->conditionValue = $conditionValue;
    }

    /**
     * @return int
     */
    public function getCustomersCount()
    {
        return $this->customersCount;
    }

    /**
     * @param int $customersCount
     */
    public function setCustomersCount($customersCount)
    {
        $this->customersCount = $customersCount;
    }

    /**
     * Remove customer.
     */
    public function removeCustomer(): void
    {
        --$this->customersCount;

        if ($this->customersCount < 0) {
            $this->customersCount = 0;
        }
    }

    /**
     * Add customer.
     */
    public function addCustomer(): void
    {
        ++$this->customersCount;
    }

    /**
     * @return float
     */
    public function getMinOrder()
    {
        return $this->minOrder;
    }

    /**
     * @param float $minOrder
     */
    public function setMinOrder($minOrder)
    {
        $this->minOrder = $minOrder;
    }

    /**
     * @param LevelPhoto $levelPhoto
     */
    public function setPhoto(LevelPhoto $levelPhoto)
    {
        $this->photo = $levelPhoto;
    }

    /**
     * @return LevelPhoto
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Removes photo.
     */
    public function removePhoto()
    {
        $this->photo = null;
    }

    /**
     * @return bool
     */
    public function hasLevelPhoto(): bool
    {
        return $this->photo instanceof LevelPhoto && $this->photo->getPath();
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->translateFieldFallback(null, 'name')->getName();
    }

    /**
     * @param null|string $name
     */
    public function setName(?string $name)
    {
        $this->translate(null, false)->setName($name);
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->translateFieldFallback(null, 'description')->getDescription();
    }

    /**
     * @param null|string $description
     */
    public function setDescription(?string $description)
    {
        $this->translate(null, false)->setDescription($description);
    }
}
