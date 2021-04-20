<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Command;

/**
 * Class RefundEarningRuleCommand.
 */
class RefundEarningRuleCommand
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $customerId;

    /**
     * RefundEarningRuleCommand constructor.
     *
     * @param string $transactionId
     * @param string $customerId
     */
    public function __construct(
        string $transactionId,
        string $customerId
    ) {
        $this->transactionId = $transactionId;
        $this->customerId = $customerId;
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }
}
