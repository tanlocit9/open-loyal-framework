<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use Assert\Assertion as Assert;

/**
 * Class EarningRuleLimit.
 */
class EarningRuleLimit
{
    const PERIOD_DAY = 'day';
    const PERIOD_WEEK = 'week';
    const PERIOD_MONTH = 'month';
    const PERIOD_3_MONTHS = '3 months';
    const PERIOD_6_MONTHS = '6 months';
    const PERIOD_YEAR = '1 year';
    const PERIOD_FOREVER = 'forever';

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var string
     */
    protected $period;

    /**
     * @var int
     */
    protected $limit;

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
     * @return string
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param string $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public static function fromArray(array $data)
    {
        $limit = new self();
        $limit->active = $data['active'];
        $limit->period = $data['period'];
        $limit->limit = $data['limit'];

        return $limit;
    }

    public static function validateRequiredData(array $data = [])
    {
        Assert::keyIsset($data, 'active');
        if ($data['active']) {
            Assert::keyExists($data, 'limit');
            Assert::keyExists($data, 'period');
            Assert::min($data['limit'], 0);
            Assert::inArray($data['period'], [static::PERIOD_DAY, static::PERIOD_WEEK, static::PERIOD_MONTH, static::PERIOD_3_MONTHS, static::PERIOD_6_MONTHS, static::PERIOD_YEAR, static::PERIOD_FOREVER]);
        }
    }
}
