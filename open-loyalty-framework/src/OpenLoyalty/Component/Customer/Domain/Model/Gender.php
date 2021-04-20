<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Model;

/**
 * Class Gender.
 */
class Gender
{
    const MALE = 'male';
    const FEMALE = 'female';
    const NOT_DISCLOSED = 'not_disclosed';

    /**
     * @var string
     */
    protected $type;

    /**
     * Gender constructor.
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        if (!in_array($type, [self::MALE, self::FEMALE, self::NOT_DISCLOSED])) {
            throw new \InvalidArgumentException('account.gender.should_be_male_female_not_disclosed');
        }

        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isMale(): bool
    {
        return $this->type == self::MALE;
    }

    /**
     * @return bool
     */
    public function isFemale(): bool
    {
        return $this->type == self::FEMALE;
    }

    /**
     * @return bool
     */
    public function isNotDisclosed(): bool
    {
        return $this->type == self::NOT_DISCLOSED;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->type;
    }
}
