<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Algorithm;

/**
 * Interface RuleNameContextInterface.
 */
interface RuleNameContextInterface
{
    /**
     * @param string $earningRuleId
     * @param string $earningRuleName
     */
    public function addEarningRuleName(string $earningRuleId, string $earningRuleName): void;

    /**
     * @return string
     */
    public function getEarningRuleNames(): string;
}
