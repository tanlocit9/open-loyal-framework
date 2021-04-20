<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\Provider;

use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Interface TransactionValueProviderInterface.
 */
interface TransactionValueProviderInterface
{
    /**
     * @param TransactionId $transactionId
     * @param bool          $includeReturns
     *
     * @return float|null
     */
    public function getTransactionValue(TransactionId $transactionId, bool $includeReturns = false): ?float;
}
