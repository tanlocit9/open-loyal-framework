<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\SystemEvent;

use OpenLoyalty\Component\Transaction\Domain\CustomerId;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class CustomerAssignedToTransactionSystemEvent.
 */
class CustomerAssignedToTransactionSystemEvent extends TransactionSystemEvent
{
    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var float
     */
    protected $grossValue = 0.0;

    /**
     * @var int
     */
    protected $amountExcludedForLevel = 0;

    /**
     * @var float
     */
    protected $grossValueWithoutDeliveryCosts = 0.0;

    /**
     * @var int
     */
    protected $transactionsCount = 0;

    /**
     * @var bool
     */
    protected $return = false;

    /**
     * @var null|string
     */
    private $revisedDocument = null;

    /**
     * @var string
     */
    private $documentNumber;

    /**
     * CustomerAssignedToTransactionSystemEvent constructor.
     *
     * @param TransactionId $transactionId
     * @param CustomerId    $customerId
     * @param float         $grossValue
     * @param float         $grossValueWithoutDeliveryCosts
     * @param string        $documentNumber
     * @param int           $amountExcludedForLevel
     * @param int|null      $transactionsCount
     * @param bool          $return
     * @param null|string   $revisedDocument
     */
    public function __construct(
        TransactionId $transactionId,
        CustomerId $customerId,
        float $grossValue,
        float $grossValueWithoutDeliveryCosts,
        string $documentNumber,
        int $amountExcludedForLevel = 0,
        ?int $transactionsCount = null,
        bool $return = false,
        ?string $revisedDocument = null
    ) {
        parent::__construct($transactionId, []);
        $this->grossValue = $grossValue;
        $this->grossValueWithoutDeliveryCosts = $grossValueWithoutDeliveryCosts;
        $this->documentNumber = $documentNumber;
        $this->customerId = $customerId;
        $this->amountExcludedForLevel = $amountExcludedForLevel;
        $this->transactionsCount = $transactionsCount;
        $this->return = $return;
        $this->revisedDocument = $revisedDocument;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return float
     */
    public function getGrossValue(): float
    {
        return $this->grossValue;
    }

    /**
     * @return float
     */
    public function getGrossValueWithoutDeliveryCosts(): float
    {
        return $this->grossValueWithoutDeliveryCosts;
    }

    /**
     * @return int
     */
    public function getAmountExcludedForLevel(): int
    {
        return $this->amountExcludedForLevel;
    }

    /**
     * @return int|null
     */
    public function getTransactionsCount(): ?int
    {
        return $this->transactionsCount;
    }

    /**
     * @return bool
     */
    public function isReturn(): bool
    {
        return $this->return;
    }

    /**
     * @return null|string
     */
    public function getRevisedDocument(): ?string
    {
        return $this->revisedDocument;
    }

    /**
     * @return string
     */
    public function getDocumentNumber(): string
    {
        return $this->documentNumber;
    }
}
