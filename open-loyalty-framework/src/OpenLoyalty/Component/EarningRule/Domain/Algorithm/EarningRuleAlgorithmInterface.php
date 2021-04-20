<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Algorithm;

use OpenLoyalty\Component\EarningRule\Domain\EarningRule;

/**
 * Interface EarningRuleAlgorithmInterface.
 */
interface EarningRuleAlgorithmInterface
{
    /**
     * @param RuleEvaluationContextInterface $context
     * @param EarningRule                    $rule
     *
     * @return bool
     */
    public function evaluate(RuleEvaluationContextInterface $context, EarningRule $rule): bool;

    /**
     * @return int
     */
    public function getPriority();
}
