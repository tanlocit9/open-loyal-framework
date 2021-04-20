<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\Command;

use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class AppendLabelsToTransaction.
 */
class AppendLabelsToTransaction extends TransactionCommand
{
    /**
     * @var array
     */
    protected $labels;

    public function __construct(TransactionId $transactionId, array $labels = [])
    {
        parent::__construct($transactionId);
        $this->labels = $labels;
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }
}
