<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Model;

use OpenLoyalty\Component\EarningRule\Domain\EarningRuleLimit as BaseLimit;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class EarningRuleLimit.
 */
class EarningRuleLimit extends BaseLimit
{
    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback()
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (!$this->active) {
            return;
        }
        if (null == $this->limit || $this->limit < 0) {
            $context->buildViolation('This value must be greater than 0')->atPath('limit')->addViolation();
        }

        if (!in_array($this->period, [
            static::PERIOD_DAY, static::PERIOD_WEEK, static::PERIOD_MONTH, static::PERIOD_FOREVER, self::PERIOD_YEAR,
            static::PERIOD_3_MONTHS, static::PERIOD_6_MONTHS,
        ])) {
            $context->buildViolation('This value is not valid.')->atPath('period')->addViolation();
        }
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
        if (!$active) {
            $this->period = null;
            $this->limit = null;
        }
    }
}
