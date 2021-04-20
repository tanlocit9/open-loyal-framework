<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Command;

use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\EarningRule\Domain\Model\EarningRulePhoto;

/**
 * Class SetEarningRulePhoto.
 */
class SetEarningRulePhoto extends EarningRuleCommand
{
    /**
     * @var EarningRulePhoto
     */
    protected $earningRulePhoto;

    /**
     * SetEarningRulePhoto constructor.
     *
     * @param EarningRuleId    $earningRuleId
     * @param EarningRulePhoto $earningRulePhoto
     */
    public function __construct(EarningRuleId $earningRuleId, EarningRulePhoto $earningRulePhoto = null)
    {
        parent::__construct($earningRuleId);
        $this->earningRulePhoto = $earningRulePhoto;
    }

    /**
     * @return EarningRulePhoto
     */
    public function getEarningRulePhoto(): EarningRulePhoto
    {
        return $this->earningRulePhoto;
    }
}
