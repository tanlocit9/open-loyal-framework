<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Command;

use OpenLoyalty\Component\EarningRule\Domain\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;

/**
 * Class CreateEarningRule.
 */
class CreateEarningRule extends EarningRuleCommand
{
    /**
     * @var array
     */
    protected $earningRuleData = [];

    /**
     * @var string
     */
    protected $type;

    public function __construct(EarningRuleId $earningRuleId, $type, $earningRuleData)
    {
        parent::__construct($earningRuleId);

        if (empty(EarningRule::TYPE_MAP[$type])) {
            throw new \InvalidArgumentException('Type: '.$type.' is not allowed');
        }

        $class = EarningRule::TYPE_MAP[$type];

        $class::validateRequiredData($earningRuleData);

        $this->type = $type;
        $this->earningRuleData = $earningRuleData;
    }

    /**
     * @return array
     */
    public function getEarningRuleData()
    {
        return $this->earningRuleData;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
