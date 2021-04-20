<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Component\Customer\Domain\Command;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\TransactionId;

/**
 * Class AssignTransactionToCustomer.
 */
class AssignTransactionToCustomer extends CustomerCommand
{
    /**
     * @var TransactionId
     */
    private $transactionId;

    /**
     * @var float
     */
    private $grossValue = 0.0;

    /**
     * @var float
     */
    private $grossValueWithoutDeliveryCosts = 0.0;

    /**
     * @var string
     */
    private $documentNumber;

    /**
     * @var int
     */
    private $amountExcludedForLevel = 0;

    /**
     * @var bool
     */
    private $isReturn = false;

    /**
     * @var null|string
     */
    private $revisedDocument = null;

    /**
     * AssignTransactionToCustomer constructor.
     *
     * @param string      $customerId
     * @param string      $transactionId
     * @param float       $grossValue
     * @param float       $grossValueWithoutDeliveryCosts
     * @param string      $documentNumber
     * @param int         $amountExcludedForLevel
     * @param bool        $isReturn
     * @param null|string $revisedDocument
     */
    public function __construct(
        string $customerId,
        string $transactionId,
        float $grossValue,
        float $grossValueWithoutDeliveryCosts,
        string $documentNumber,
        int $amountExcludedForLevel,
        bool $isReturn,
        ?string $revisedDocument = null
    ) {
        parent::__construct(new CustomerId($customerId));

        $this->transactionId = new TransactionId($transactionId);
        $this->grossValue = $grossValue;
        $this->grossValueWithoutDeliveryCosts = $grossValueWithoutDeliveryCosts;
        $this->documentNumber = $documentNumber;
        $this->amountExcludedForLevel = $amountExcludedForLevel;
        $this->isReturn = $isReturn;
        $this->revisedDocument = $revisedDocument;
    }

    /**
     * @return TransactionId
     */
    public function getTransactionId(): TransactionId
    {
        return $this->transactionId;
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
     * @return string
     */
    public function getDocumentNumber(): string
    {
        return $this->documentNumber;
    }

    /**
     * @return int
     */
    public function getAmountExcludedForLevel(): int
    {
        return $this->amountExcludedForLevel;
    }

    /**
     * @return bool
     */
    public function isReturn(): bool
    {
        return $this->isReturn;
    }

    /**
     * @return null|string
     */
    public function getRevisedDocument(): ?string
    {
        return $this->revisedDocument;
    }
}
