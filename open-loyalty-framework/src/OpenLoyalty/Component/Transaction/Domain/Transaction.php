<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain;

use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Core\Domain\Model\SKU;
use OpenLoyalty\Component\Core\Domain\SnapableEventSourcedAggregateRoot;
use OpenLoyalty\Component\Transaction\Domain\Event\CustomerWasAssignedToTransaction;
use OpenLoyalty\Component\Transaction\Domain\Event\LabelsWereAppendedToTransaction;
use OpenLoyalty\Component\Transaction\Domain\Event\LabelsWereUpdated;
use OpenLoyalty\Component\Transaction\Domain\Event\TransactionWasRegistered;
use OpenLoyalty\Component\Transaction\Domain\Model\CustomerBasicData;
use OpenLoyalty\Component\Transaction\Domain\Model\Item;

/**
 * Class Transaction.
 */
class Transaction extends SnapableEventSourcedAggregateRoot
{
    const TYPE_RETURN = 'return';
    const TYPE_SELL = 'sell';

    /**
     * @var TransactionId
     */
    protected $transactionId;

    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var PosId|null
     */
    protected $posId;

    /**
     * @var string
     */
    protected $documentNumber;

    /**
     * @var string
     */
    protected $documentType;

    /**
     * @var \DateTime
     */
    protected $purchaseDate;

    /**
     * @var string
     */
    protected $purchasePlace;

    /**
     * @var array
     */
    protected $excludedDeliverySKUs = [];

    /**
     * @var array
     */
    protected $excludedLevelSKUs = [];

    /**
     * @var array
     */
    protected $excludedLevelCategories = [];

    /**
     * @var CustomerBasicData
     */
    protected $customerData;

    /**
     * @var Item[]
     */
    protected $items = [];

    /**
     * @var string|null
     */
    protected $revisedDocument;

    /**
     * @var Label[]
     */
    protected $labels = [];

    /**
     * @return string
     */
    public function getAggregateRootId(): string
    {
        return $this->transactionId;
    }

    /**
     * @param TransactionId $transactionId
     * @param array         $transactionData
     * @param array         $customerData
     * @param array         $items
     * @param PosId|null    $posId
     * @param array|null    $excludedDeliverySKUs
     * @param array|null    $excludedLevelSKUs
     * @param array|null    $excludedLevelCategories
     * @param string|null   $revisedDocument
     * @param array         $labels
     *
     * @return Transaction
     */
    public static function createTransaction(
        TransactionId $transactionId,
        array $transactionData,
        array $customerData,
        array $items,
        PosId $posId = null,
        array $excludedDeliverySKUs = null,
        array $excludedLevelSKUs = null,
        array $excludedLevelCategories = null,
        string $revisedDocument = null,
        array $labels = []
    ): Transaction {
        $transaction = new self();
        $transaction->create(
            $transactionId,
            $transactionData,
            $customerData,
            $items,
            $posId,
            $excludedDeliverySKUs,
            $excludedLevelSKUs,
            $excludedLevelCategories,
            $revisedDocument,
            $labels
        );

        return $transaction;
    }

    /**
     * @param CustomerId  $customerId
     * @param string|null $email
     * @param string|null $phone
     */
    public function assignCustomerToTransaction(
        CustomerId $customerId,
        string $email = null,
        string $phone = null
    ): void {
        $this->apply(
            new CustomerWasAssignedToTransaction($this->transactionId, $customerId, $email, $phone)
        );
    }

    /**
     * @param array $labels
     */
    public function appendLabels(array $labels = []): void
    {
        $this->apply(
            new LabelsWereAppendedToTransaction($this->transactionId, $labels)
        );
    }

    /**
     * @param array $labels
     */
    public function setLabels(array $labels = []): void
    {
        $this->apply(
            new LabelsWereUpdated($this->transactionId, $labels)
        );
    }

    /**
     * @param TransactionId $transactionId
     * @param array         $transactionData
     * @param array         $customerData
     * @param array         $items
     * @param PosId|null    $posId
     * @param array|null    $excludedDeliverySKUs
     * @param array|null    $excludedLevelSKUs
     * @param array|null    $excludedLevelCategories
     * @param string|null   $revisedDocument
     * @param array         $labels
     */
    private function create(
        TransactionId $transactionId,
        array $transactionData,
        array $customerData,
        array $items,
        PosId $posId = null,
        array $excludedDeliverySKUs = null,
        array $excludedLevelSKUs = null,
        array $excludedLevelCategories = null,
        string $revisedDocument = null,
        array $labels = []
    ): void {
        $this->apply(
            new TransactionWasRegistered(
                $transactionId,
                $transactionData,
                $customerData,
                $items,
                $posId,
                $excludedDeliverySKUs,
                $excludedLevelSKUs,
                $excludedLevelCategories,
                $revisedDocument,
                $labels
            )
        );
    }

    /**
     * @param TransactionWasRegistered $event
     */
    protected function applyTransactionWasRegistered(TransactionWasRegistered $event): void
    {
        $documentData = $event->getTransactionData();
        $this->transactionId = $event->getTransactionId();
        $this->documentType = $documentData['documentType'];
        $this->documentNumber = $documentData['documentNumber'];
        $this->purchaseDate = $documentData['purchaseDate'];
        $this->purchasePlace = $documentData['purchasePlace'];
        $this->customerData = CustomerBasicData::deserialize($event->getCustomerData());
        $this->items = $event->getItems();
        $this->excludedDeliverySKUs = $event->getExcludedDeliverySKUs();
        $this->excludedLevelSKUs = $event->getExcludedLevelSKUs();
        $this->excludedLevelCategories = $event->getExcludedLevelCategories();
        $this->revisedDocument = $event->getRevisedDocument();
        $this->posId = $event->getPosId();
        $this->labels = $event->getLabels();
    }

    /**
     * @param CustomerWasAssignedToTransaction $event
     */
    protected function applyCustomerWasAssignedToTransaction(CustomerWasAssignedToTransaction $event): void
    {
        $this->customerId = $event->getCustomerId();
        $this->customerData->updateEmailAndPhone(
            $event->getEmail(),
            $event->getPhone()
        );
    }

    /**
     * @param LabelsWereAppendedToTransaction $event
     */
    protected function applyLabelsWereAppendedToTransaction(LabelsWereAppendedToTransaction $event): void
    {
        $this->labels = array_merge($this->labels, $event->getLabels());
    }

    /**
     * @param LabelsWereUpdated $event
     */
    protected function applyLabelsWereUpdated(LabelsWereUpdated $event): void
    {
        $this->labels = $event->getLabels();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->getTransactionId();
    }

    /**
     * @return TransactionId
     */
    public function getTransactionId(): TransactionId
    {
        return $this->transactionId;
    }

    /**
     * @return CustomerId|null
     */
    public function getCustomerId(): ?CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return PosId|null
     */
    public function getPosId(): ?PosId
    {
        return $this->posId;
    }

    /**
     * @return string
     */
    public function getDocumentNumber(): string
    {
        return $this->documentNumber;
    }

    /**
     * @return string
     */
    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    /**
     * @return \DateTime
     */
    public function getPurchaseDate(): \DateTime
    {
        return $this->purchaseDate;
    }

    /**
     * @return string
     */
    public function getPurchasePlace(): string
    {
        return $this->purchasePlace;
    }

    /**
     * @return array
     */
    public function getExcludedDeliverySKUs(): array
    {
        return $this->excludedDeliverySKUs;
    }

    /**
     * @return array
     */
    public function getExcludedLevelSKUs(): array
    {
        return $this->excludedLevelSKUs;
    }

    /**
     * @return array
     */
    public function getExcludedLevelCategories(): array
    {
        return $this->excludedLevelCategories;
    }

    /**
     * @return CustomerBasicData
     */
    public function getCustomerData(): CustomerBasicData
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
     * @return string|null
     */
    public function getRevisedDocument(): ?string
    {
        return $this->revisedDocument;
    }

    /**
     * @return Label[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @return float
     */
    public function getAmountExcludedForLevel(): float
    {
        if (!$this->excludedLevelSKUs) {
            $excludedSKUs = [];
        } else {
            $excludedSKUs = array_map(
                function ($obj) {
                    if ($obj instanceof SKU) {
                        return $obj->getCode();
                    }

                    return $obj;
                },
                $this->getExcludedLevelSKUs()
            );
        }

        if (!$this->excludedLevelCategories) {
            $excludedCategories = [];
        } else {
            $excludedCategories = array_map(
                function ($obj) {
                    return $obj;
                },
                $this->getExcludedLevelCategories()
            );
        }

        $amountSKUs = array_reduce(
            $this->items,
            function ($carry, Item $item) use ($excludedSKUs) {
                if (!in_array($item->getSku()->getCode(), $excludedSKUs)) {
                    return $carry;
                }
                $carry += $item->getGrossValue();

                return $carry;
            },
            0
        );

        $amountCategories = array_reduce(
            $this->items,
            function ($carry, Item $item) use ($excludedCategories) {
                if (!in_array($item->getCategory(), $excludedCategories)) {
                    return $carry;
                }
                $carry += $item->getGrossValue();

                return $carry;
            },
            0
        );

        return $amountSKUs + $amountCategories;
    }

    /**
     * @param array $excludeSKUs
     * @param array $excludeLabels
     * @param array $includeLabels
     * @param bool  $excludeDelivery
     *
     * @return Item[]
     */
    public function getFilteredItems(
        array $excludeSKUs = [],
        array $excludeLabels = [],
        array $includeLabels = [],
        $excludeDelivery = false
    ): array {
        /** @var string[] $excludeSKUs */
        $excludeSKUs = array_map(
            function (SKU $sku) {
                return $sku instanceof SKU ? $sku->getCode() : $sku;
            },
            $excludeSKUs
        );

        /** @var Label[] $excludeLabels */
        $excludeLabels = array_map(
            function ($label) {
                return $label instanceof Label ? $label : new Label($label['key'], $label['value']);
            },
            $excludeLabels
        );
        /** @var Label[] $includeLabels */
        $includeLabels = array_map(
            function ($label) {
                return $label instanceof Label ? $label : new Label($label['key'], $label['value']);
            },
            $includeLabels
        );

        if ($excludeDelivery && !empty($this->excludedDeliverySKUs)) {
            $excludeSKUs = array_merge($excludeSKUs, $this->excludedDeliverySKUs);
        }

        return array_filter(
            $this->items,
            function (Item $item) use ($excludeSKUs, $excludeLabels, $includeLabels) {
                // filter items by SKU
                if (in_array($item->getSku()->getCode(), $excludeSKUs)) {
                    return false;
                }

                if (count($excludeLabels) > 0) {
                    // filter items by Label
                    foreach ($excludeLabels as $excludeLabel) {
                        foreach ($item->getLabels() as $label) {
                            if ($label->getKey() == $excludeLabel->getKey()
                                && $label->getValue() == $excludeLabel->getValue()
                            ) {
                                return false;
                            }
                        }
                    }
                } elseif (count($includeLabels) > 0) {
                    // filter items by Label
                    $productHasLabel = false;
                    foreach ($includeLabels as $includeLabel) {
                        foreach ($item->getLabels() as $label) {
                            if ($label->getKey() === $includeLabel->getKey()
                                && $label->getValue() === $includeLabel->getValue()
                            ) {
                                $productHasLabel = true;
                                break;
                            }
                        }
                    }
                    if (!$productHasLabel) {
                        return false;
                    }
                }

                return true;
            }
        );
    }

    /**
     * @param array $excludeAdditionalSKUs
     * @param array $excludeLabels
     * @param array $includedLabels
     * @param bool  $excludeDelivery
     *
     * @return float
     */
    public function getGrossValue(
        array $excludeAdditionalSKUs = [],
        array $excludeLabels = [],
        array $includedLabels = [],
        $excludeDelivery = false
    ): float {
        $filteredItems = $this->getFilteredItems($excludeAdditionalSKUs, $excludeLabels, $includedLabels, $excludeDelivery);

        return array_reduce(
            $filteredItems,
            function ($carry, Item $item) {
                return $carry + $item->getGrossValue();
            },
            0
        );
    }

    /**
     * @param array $excludeAdditionalSKUs
     * @param array $excludeLabels
     * @param array $includedLabels
     *
     * @return float
     */
    public function getGrossValueWithoutDeliveryCosts(
        array $excludeAdditionalSKUs = [],
        array $excludeLabels = [],
        array $includedLabels = []
    ): float {
        return $this->getGrossValue($excludeAdditionalSKUs, $excludeLabels, $includedLabels, true);
    }
}
