<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\SystemEvent;

use OpenLoyalty\Component\Transaction\Domain\Model\Item;
use OpenLoyalty\Component\Transaction\Domain\PosId;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class TransactionRegisteredEvent.
 */
class TransactionRegisteredEvent
{
    /**
     * @var TransactionId
     */
    protected $transactionId;

    /**
     * @var array
     */
    protected $transactionData;

    /**
     * @var array
     */
    protected $customerData;

    /**
     * @var Item[]
     */
    protected $items;

    /**
     * @var PosId|null
     */
    protected $posId;

    /**
     * TransactionRegisteredEvent constructor.
     *
     * @param TransactionId $transactionId
     * @param array         $transactionData
     * @param array         $customerData
     * @param Item[]        $items
     * @param PosId|null    $posId
     */
    public function __construct(TransactionId $transactionId, array $transactionData, array $customerData, array
    $items, ?PosId $posId = null)
    {
        $this->transactionId = $transactionId;
        $this->transactionData = $transactionData;
        $this->customerData = $customerData;
        $this->items = $items;
        $this->posId = $posId;
    }

    /**
     * @return TransactionId
     */
    public function getTransactionId(): TransactionId
    {
        return $this->transactionId;
    }

    /**
     * @return array
     */
    public function getTransactionData(): array
    {
        return $this->transactionData;
    }

    /**
     * @return array
     */
    public function getCustomerData(): array
    {
        return $this->customerData;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return PosId|null
     */
    public function getPosId(): ?PosId
    {
        return $this->posId;
    }
}
