<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\LevelBundle\Model;

use OpenLoyalty\Component\Level\Domain\Level as BaseLevel;
use OpenLoyalty\Component\Level\Domain\LevelTranslation;

/**
 * Class Level.
 */
class Level extends BaseLevel
{
    /**
     * Level constructor.
     */
    public function __construct()
    {
    }

    /**
     * @var Reward
     */
    protected $reward;

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
        $this->reward = $reward;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $this->mergeNewTranslations();

        $specialRewards = array_map(function (SpecialReward $specialReward) {
            return $specialReward->toArray();
        }, $this->getSpecialRewards());

        $translations = array_map(
            function (LevelTranslation $level): array {
                return [
                    'name' => $level->getName(),
                    'description' => $level->getDescription(),
                ];
            },
            $this->getTranslations()->toArray()
        );

        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'active' => $this->isActive(),
            'conditionValue' => $this->getConditionValue(),
            'reward' => $this->getReward()->toArray(),
            'specialRewards' => $specialRewards,
            'minOrder' => $this->getMinOrder(),
            'translations' => $translations,
        ];
    }
}
