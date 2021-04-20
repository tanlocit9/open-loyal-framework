<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Algorithm;

/**
 * Class RuleNameContext.
 */
class RuleNameContext implements RuleNameContextInterface
{
    /**
     * @var array
     */
    private $earningRuleNames = [];

    /**
     * {@inheritdoc}
     */
    public function addEarningRuleName(string $earningRuleId, string $earningRuleName): void
    {
        $this->earningRuleNames[$earningRuleId] = $earningRuleName;
    }

    /**
     * {@inheritdoc}
     */
    public function getEarningRuleNames(): string
    {
        return implode(', ', $this->earningRuleNames);
    }
}
