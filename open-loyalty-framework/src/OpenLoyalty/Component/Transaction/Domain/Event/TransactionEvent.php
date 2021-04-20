<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\Event;

use Broadway\Serializer\Serializable;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class TransactionEvent.
 */
abstract class TransactionEvent implements Serializable
{
    /**
     * @var TransactionId
     */
    protected $transactionId;

    /**
     * TransactionEvent constructor.
     *
     * @param TransactionId $transactionId
     */
    public function __construct(TransactionId $transactionId)
    {
        $this->transactionId = $transactionId;
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
    public function serialize(): array
    {
        return [
            'transactionId' => $this->transactionId->__toString(),
        ];
    }
}
