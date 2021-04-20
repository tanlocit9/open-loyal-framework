<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\Event;

use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Transaction\Domain\Model\Item;
use OpenLoyalty\Component\Transaction\Domain\PosId;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class TransactionWasRegistered.
 */
class TransactionWasRegistered extends TransactionEvent
{
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
     * @var PosId
     */
    protected $posId;

    /**
     * @var array
     */
    protected $excludedDeliverySKUs;

    /**
     * @var array
     */
    protected $excludedLevelSKUs;

    /**
     * @var array
     */
    protected $excludedLevelCategories;

    protected $revisedDocument;

    /**
     * @var Label[]
     */
    protected $labels;

    /**
     * TransactionEvent constructor.
     *
     * @param TransactionId $transactionId
     * @param array         $transactionData
     * @param array         $customerData
     * @param Item[]        $items
     * @param PosId         $posId
     * @param array         $excludedDeliverySKUs
     * @param array         $excludedLevelSKUs
     * @param array         $excludedLevelCategories
     * @param null          $revisedDocument
     * @param array         $labels
     */
    public function __construct(
        TransactionId $transactionId,
        array $transactionData,
        array $customerData,
        array $items = [],
        PosId $posId = null,
        array $excludedDeliverySKUs = null,
        array $excludedLevelSKUs = null,
        array $excludedLevelCategories = null,
        $revisedDocument = null,
        array $labels = []
    ) {
        parent::__construct($transactionId);
        $itemsObjects = [];
        foreach ($items as $item) {
            if ($item instanceof Item) {
                $itemsObjects[] = $item;
            } else {
                $itemsObjects[] = Item::deserialize($item);
            }
        }
        $transactionLabels = [];
        foreach ($labels as $label) {
            if ($label instanceof Label) {
                $transactionLabels[] = $label;
            } else {
                $transactionLabels[] = Label::deserialize($label);
            }
        }
        $this->labels = $transactionLabels;

        if (is_numeric($transactionData['purchaseDate'])) {
            $tmp = new \DateTime();
            $tmp->setTimestamp($transactionData['purchaseDate']);
            $transactionData['purchaseDate'] = $tmp;
        }

        $this->transactionData = $transactionData;
        $this->customerData = $customerData;
        $this->items = $itemsObjects;
        $this->posId = $posId;
        $this->excludedDeliverySKUs = $excludedDeliverySKUs;
        $this->excludedLevelSKUs = $excludedLevelSKUs;
        $this->excludedLevelCategories = $excludedLevelCategories;
        $this->revisedDocument = $revisedDocument;
    }

    /**
     * @return array
     */
    public function getTransactionData()
    {
        return $this->transactionData;
    }

    /**
     * @return array
     */
    public function getCustomerData()
    {
        return $this->customerData;
    }

    /**
     * @return \OpenLoyalty\Component\Transaction\Domain\Model\Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return PosId
     */
    public function getPosId()
    {
        return $this->posId;
    }

    /**
     * @return array
     */
    public function getExcludedDeliverySKUs()
    {
        return $this->excludedDeliverySKUs;
    }

    /**
     * @return array
     */
    public function getExcludedLevelSKUs()
    {
        return $this->excludedLevelSKUs;
    }

    /**
     * @return array
     */
    public function getExcludedLevelCategories()
    {
        return $this->excludedLevelCategories;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        $items = [];
        foreach ($this->items as $item) {
            $items[] = $item->serialize();
        }
        $labels = [];
        foreach ($this->labels as $label) {
            $labels[] = $label->serialize();
        }
        $customerData = $this->customerData;

        $transactionData = $this->transactionData;

        if ($transactionData['purchaseDate'] instanceof \DateTime) {
            $transactionData['purchaseDate'] = $transactionData['purchaseDate']->getTimestamp();
        }

        return array_merge(parent::serialize(), [
            'transactionId' => $this->transactionId->__toString(),
            'transactionData' => $transactionData,
            'customerData' => $customerData,
            'items' => $items,
            'posId' => $this->posId ? $this->posId->__toString() : null,
            'excludedDeliverySKUs' => $this->excludedDeliverySKUs,
            'excludedLevelSKUs' => $this->excludedLevelSKUs,
            'excludedLevelCategories' => $this->excludedLevelCategories,
            'revisedDocument' => $this->revisedDocument,
            'labels' => $labels,
        ]);
    }

    public static function deserialize(array $data)
    {
        $items = [];
        foreach ($data['items'] as $item) {
            $items[] = Item::deserialize($item);
        }
        $labels = [];
        if (array_key_exists('labels', $data)) {
            foreach ($data['labels'] as $label) {
                $labels[] = Label::deserialize($label);
            }
        }

        $transactionData = $data['transactionData'];
        if (is_numeric($transactionData['purchaseDate'])) {
            $tmp = new \DateTime();
            $tmp->setTimestamp($transactionData['purchaseDate']);
            $transactionData['purchaseDate'] = $tmp;
        }
        $customerData = $data['customerData'];

        return new self(
            new TransactionId($data['transactionId']),
            $transactionData,
            $customerData,
            $items,
            isset($data['posId']) && $data['posId'] ? new PosId($data['posId']) : null,
            isset($data['excludedDeliverySKUs']) ? $data['excludedDeliverySKUs'] : null,
            isset($data['excludedLevelSKUs']) ? $data['excludedLevelSKUs'] : null,
            isset($data['excludedLevelCategories']) ? $data['excludedLevelCategories'] : null,
            isset($data['revisedDocument']) ? $data['revisedDocument'] : null,
            $labels
        );
    }

    public function getRevisedDocument()
    {
        return $this->revisedDocument;
    }

    /**
     * @return Label[]
     */
    public function getLabels()
    {
        return $this->labels;
    }
}
