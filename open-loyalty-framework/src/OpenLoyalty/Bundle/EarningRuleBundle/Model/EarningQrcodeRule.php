<?php
/**
 * Copyright ÂŠ 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Model;

/**
 * Class EarningQrcodeRule.
 */
class EarningQrcodeRule
{
    /**
     * @var string|null
     */
    protected $code;

    /**
     * @var string|null
     */
    protected $earningRuleId;

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * @return string|null
     */
    public function getEarningRuleId(): ?string
    {
        return $this->earningRuleId;
    }

    /**
     * @param $earningRuleId
     */
    public function setEarningRuleId(string $earningRuleId)
    {
        $this->earningRuleId = $earningRuleId;
    }
}
