<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\LevelBundle\Model;

use OpenLoyalty\Component\Level\Domain\Model\Reward as BaseReward;

/**
 * Class Reward.
 */
class Reward extends BaseReward
{
    public function __construct()
    {
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param float $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'value' => $this->getValue(),
            'code' => $this->getCode(),
        ];
    }
}
