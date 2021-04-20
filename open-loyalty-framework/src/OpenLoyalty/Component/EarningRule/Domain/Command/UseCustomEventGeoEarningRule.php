<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Command;

use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\EarningRule\Domain\Model\UsageSubject;

/**
 * Class UseCustomEventEarningRule.
 */
class UseCustomEventGeoEarningRule extends EarningRuleCommand
{
    /**
     * @var UsageSubject
     */
    protected $subject;

    public function __construct(EarningRuleId $earningRuleId, UsageSubject $subject)
    {
        parent::__construct($earningRuleId);
        $this->subject = $subject;
    }

    /**
     * @return UsageSubject
     */
    public function getSubject()
    {
        return $this->subject;
    }
}
