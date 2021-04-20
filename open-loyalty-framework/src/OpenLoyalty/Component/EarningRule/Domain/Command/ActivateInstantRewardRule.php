<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Command;

use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;

/**
 * Class ActivateInstantRewardRule.
 */
class ActivateInstantRewardRule extends ActivateEarningRule
{
    /**
     * @var string
     */
    private $customerId;

    /**
     * @var float
     */
    private $transactionValue;

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $campaignId;

    /**
     * EarningRuleCommand constructor.
     *
     * @param EarningRuleId $earningRuleId
     * @param string        $customerId
     * @param float         $transactionValue
     * @param string        $transactionId
     * @param string        $campaignId
     */
    public function __construct(
        EarningRuleId $earningRuleId,
        string $customerId,
        float $transactionValue,
        string $transactionId,
        string $campaignId
    ) {
        parent::__construct($earningRuleId);
        $this->customerId = $customerId;
        $this->transactionValue = $transactionValue;
        $this->transactionId = $transactionId;
        $this->campaignId = $campaignId;
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getTransactionValue(): string
    {
        return $this->transactionValue;
    }

    /**
     * @return string
     */
    public function getCampaignId(): string
    {
        return $this->campaignId;
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }
}
