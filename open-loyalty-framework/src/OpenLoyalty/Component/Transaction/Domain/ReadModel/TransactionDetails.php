<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\ReadModel;

use Broadway\ReadModel\SerializableReadModel;
use OpenLoyalty\Component\Core\Domain\ReadModel\Versionable;
use OpenLoyalty\Component\Core\Domain\ReadModel\VersionableReadModel;
use OpenLoyalty\Component\Transaction\Domain\CustomerId;
use OpenLoyalty\Component\Transaction\Domain\Model\CustomerBasicData;
use OpenLoyalty\Component\Transaction\Domain\Model\Item;
use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Transaction\Domain\Model\Item as TransactionItem;
use OpenLoyalty\Component\Transaction\Domain\PosId;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class TransactionDetails.
 */
class TransactionDetails implements SerializableReadModel, VersionableReadModel
{
    use Versionable;

    /**
     * @var TransactionId
     */
    protected $transactionId;

    /**
     * @var string
     */
    protected $documentNumber;

    /**
     * @var \DateTime
     */
    protected $purchaseDate;

    /**
     * @var string|null
     */
    protected $purchasePlace = null;

    /**
     * @var string
     */
    protected $documentType;

    /**
     * @var CustomerId|null
     */
    protected $customerId;

    /**
     * @var CustomerBasicData
     */
    protected $customerData;

    /**
     * @var Item[]
     */
    protected $items = [];

    /**
     * @var Label[]
     */
    protected $labels = [];

    /**
     * @var PosId|null
     */
    protected $posId = null;

    /**
     * @var array|null
     */
    protected $excludedDeliverySKUs = null;

    /**
     * @var array|null
     */
    protected $excludedLevelSKUs = null;

    /**
     * @var array|null
     */
    protected $excludedLevelCategories = null;

    /**
     * @var string|null
     */
    protected $revisedDocument = null;

    /**
     * @var float
     */
    protected $grossValue = 0.0;

    /**
     * TransactionDetails constructor.
     *
     * @param TransactionId $transactionId
     */
    public function __construct(TransactionId $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->transactionId;
    }

    /**
     * {@inheritdoc}
     *
     * @return TransactionDetails
     */
    public static function deserialize(array $data)
    {
        $items = [];
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $items[] = Item::deserialize($item);
            }
        }

        $labels = [];
        if (isset($data['labels'])) {
            foreach ($data['labels'] as $label) {
                $labels[] = Label::deserialize($label);
            }
        }

        if (is_numeric($data['purchaseDate'])) {
            $tmp = new \DateTime();
            $tmp->setTimestamp($data['purchaseDate']);
            $data['purchaseDate'] = $tmp;
        }
        $customerData = $data['customerData'];

        $transaction = new self(new TransactionId($data['transactionId']));
        $transaction->labels = $labels;

        $transaction->customerData = CustomerBasicData::deserialize($customerData);
        $transaction->items = $items;
        if (!empty($data['customerId'])) {
            $transaction->customerId = new CustomerId($data['customerId']);
        }
        $transaction->documentNumber = $data['documentNumber'];
        $transaction->documentType = isset($data['documentType']) ? $data['documentType'] : Transaction::TYPE_SELL;
        $transaction->purchasePlace = $data['purchasePlace'];
        $transaction->purchaseDate = $data['purchaseDate'];
        $transaction->grossValue = $data['grossValue'];
        $transaction->revisedDocument = isset($data['revisedDocument']) ? $data['revisedDocument'] : null;
        if (isset($data['excludedDeliverySKUs'])) {
            $transaction->excludedDeliverySKUs = json_decode($data['excludedDeliverySKUs'], true);
        }
        if (isset($data['excludedLevelSKUs'])) {
            $transaction->excludedLevelSKUs = json_decode($data['excludedLevelSKUs'], true);
        }
        if (isset($data['excludedLevelCategories'])) {
            $transaction->excludedLevelCategories = json_decode($data['excludedLevelCategories'], true);
        }

        if (isset($data['posId'])) {
            $transaction->posId = new PosId($data['posId']);
        }

        return $transaction;
    }

    /**
     * {@inheritdoc}
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

        return [
            'customerId' => $this->customerId ? (string) $this->customerId : null,
            'transactionId' => (string) $this->transactionId,
            'documentType' => $this->documentType,
            'documentNumber' => $this->documentNumber,
            'documentNumberRaw' => $this->documentNumber,
            'purchaseDate' => $this->purchaseDate->getTimestamp(),
            'purchasePlace' => $this->purchasePlace,
            'customerData' => $this->customerData->serialize(),
            'items' => $items,
            'posId' => $this->posId ? (string) $this->posId : null,
            'excludedDeliverySKUs' => $this->excludedDeliverySKUs ? json_encode($this->excludedDeliverySKUs) : null,
            'excludedLevelSKUs' => $this->excludedLevelSKUs ? json_encode($this->excludedLevelSKUs) : null,
            'excludedLevelCategories' => $this->excludedLevelCategories ? json_encode(
                $this->excludedLevelCategories
            ) : null,
            'revisedDocument' => $this->revisedDocument,
            'labels' => $labels,
            'grossValue' => $this->grossValue,
        ];
    }

    /**
     * @return TransactionId
     */
    public function getTransactionId(): TransactionId
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function getDocumentNumber(): string
    {
        return $this->documentNumber;
    }

    /**
     * @param string $documentNumber
     */
    public function setDocumentNumber(string $documentNumber): void
    {
        $this->documentNumber = $documentNumber;
    }

    /**
     * @return \DateTime|null
     */
    public function getPurchaseDate(): ?\DateTime
    {
        return $this->purchaseDate;
    }

    /**
     * @param \DateTime $purchaseDate
     */
    public function setPurchaseDate(\DateTime $purchaseDate): void
    {
        $this->purchaseDate = $purchaseDate;
    }

    /**
     * @return string|null
     */
    public function getPurchasePlace(): ?string
    {
        return $this->purchasePlace;
    }

    /**
     * @param string|null $purchasePlace
     */
    public function setPurchasePlace(?string $purchasePlace): void
    {
        $this->purchasePlace = $purchasePlace;
    }

    /**
     * @return string
     */
    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    /**
     * @param string $documentType
     */
    public function setDocumentType(string $documentType): void
    {
        $this->documentType = $documentType;
    }

    /**
     * @return CustomerId|null
     */
    public function getCustomerId(): ?CustomerId
    {
        return $this->customerId;
    }

    /**
     * @param CustomerId $customerId
     */
    public function setCustomerId(CustomerId $customerId): void
    {
        $this->customerId = $customerId;
    }

    /**
     * @return CustomerBasicData
     */
    public function getCustomerData(): CustomerBasicData
    {
        return $this->customerData;
    }

    /**
     * @param CustomerBasicData $customerData
     */
    public function setCustomerData(CustomerBasicData $customerData): void
    {
        $this->customerData = $customerData;
    }

    /**
     * @return TransactionItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param TransactionItem[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return PosId|null
     */
    public function getPosId(): ?PosId
    {
        return $this->posId;
    }

    /**
     * @param PosId|null $posId
     */
    public function setPosId(?PosId $posId): void
    {
        $this->posId = $posId;
    }

    /**
     * @return array
     */
    public function getExcludedDeliverySKUs(): array
    {
        return $this->excludedDeliverySKUs;
    }

    /**
     * @param array|null $excludedDeliverySKUs
     */
    public function setExcludedDeliverySKUs(?array $excludedDeliverySKUs): void
    {
        $this->excludedDeliverySKUs = $excludedDeliverySKUs;
    }

    /**
     * @return array
     */
    public function getExcludedLevelSKUs(): array
    {
        return $this->excludedLevelSKUs;
    }

    /**
     * @param array $excludedLevelSKUs
     */
    public function setExcludedLevelSKUs(?array $excludedLevelSKUs): void
    {
        $this->excludedLevelSKUs = $excludedLevelSKUs;
    }

    /**
     * @return array
     */
    public function getExcludedLevelCategories(): array
    {
        return $this->excludedLevelCategories;
    }

    /**
     * @param array $excludedLevelCategories
     */
    public function setExcludedLevelCategories(?array $excludedLevelCategories): void
    {
        $this->excludedLevelCategories = $excludedLevelCategories;
    }

    /**
     * @return string|null
     */
    public function getRevisedDocument(): ?string
    {
        return $this->revisedDocument;
    }

    /**
     * @param string|null $revisedDocument
     */
    public function setRevisedDocument(?string $revisedDocument)
    {
        $this->revisedDocument = $revisedDocument;
    }

    /**
     * @return Label[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @param Label[] $labels
     */
    public function setLabels(array $labels): void
    {
        $this->labels = $labels;
    }

    /**
     * @param array $labels
     */
    public function appendLabels(array $labels): void
    {
        $this->labels = array_merge($this->labels, $labels);
    }

    /**
     * @return float
     */
    public function getGrossValue(): float
    {
        return $this->grossValue;
    }

    /**
     * @param float $grossValue
     */
    public function setGrossValue(float $grossValue): void
    {
        $this->grossValue = $grossValue;
    }
}
