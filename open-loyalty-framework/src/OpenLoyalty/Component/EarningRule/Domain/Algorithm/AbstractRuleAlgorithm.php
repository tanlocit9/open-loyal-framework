<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Algorithm;

/**
 * Class AbstractRuleAlgorithm.
 */
abstract class AbstractRuleAlgorithm implements EarningRuleAlgorithmInterface
{
    const HIGH_PRIORITY = 1;
    const MEDIUM_PRIORITY = 2;
    const LOW_PRIORITY = 3;

    /**
     * @var int
     */
    protected $priority;

    /**
     * AbstractRuleAlgorithm constructor.
     *
     * @param int $priority
     */
    public function __construct($priority = self::HIGH_PRIORITY)
    {
        $this->priority = $priority;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->priority;
    }
}
