<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Strategy;

use OpenLoyalty\Component\EarningRule\Domain\Algorithm\RuleEvaluationContextInterface;
use OpenLoyalty\Component\EarningRule\Domain\EarningRule;

/**
 * Interface EarningRuleStrategy.
 */
interface EarningRuleStrategy
{
    /**
     * @param RuleEvaluationContextInterface $context
     * @param EarningRule                    $rule
     *
     * @return bool
     */
    public function isApplicable(RuleEvaluationContextInterface $context, EarningRule $rule): bool;
}
