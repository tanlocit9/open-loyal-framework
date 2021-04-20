<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\SystemEvent;

use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class TransactionSystemEvent.
 */
class TransactionSystemEvent
{
    /**
     * @var TransactionId
     */
    protected $transactionId;

    /**
     * @var array
     */
    protected $customerData;

    /**
     * TransactionEvent constructor.
     *
     * @param TransactionId $transactionId
     * @param array         $customerData
     */
    public function __construct(TransactionId $transactionId, array $customerData)
    {
        $this->transactionId = $transactionId;
        $this->customerData = $customerData;
    }

    /**
     * @return TransactionId
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @return array
     */
    public function getCustomerData()
    {
        return $this->customerData;
    }

    /**
     * @param array $customerData
     */
    public function setCustomerData($customerData)
    {
        $this->customerData = $customerData;
    }
}
