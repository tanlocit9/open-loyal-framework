<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Model;

use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\TransactionId;

/**
 * Class SpendPointsTransfer.
 */
class SpendPointsTransfer extends PointsTransfer
{
    /**
     * @var TransactionId
     */
    protected $transactionId;

    /**
     * @var TransactionId
     */
    protected $revisedTransactionId;

    /**
     * PointsTransfer constructor.
     *
     * @param PointsTransferId   $id
     * @param float              $value
     * @param \DateTime          $createdAt
     * @param bool               $canceled
     * @param string             $comment
     * @param string             $issuer
     * @param TransactionId|null $transactionId
     * @param TransactionId|null $revisedTransactionId
     */
    public function __construct(
        PointsTransferId $id,
        float $value,
        \DateTime $createdAt = null,
        $canceled = false,
        $comment = null,
        $issuer = self::ISSUER_SYSTEM,
        TransactionId $transactionId = null,
        TransactionId $revisedTransactionId = null
    ) {
        parent::__construct($id, $value, $createdAt, $canceled, $comment, $issuer);
        $this->transactionId = $transactionId;
        $this->revisedTransactionId = $revisedTransactionId;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        $createdAt = null;
        if (isset($data['createdAt'])) {
            $createdAt = new \DateTime();
            $createdAt->setTimestamp($data['createdAt']);
        }

        $transfer = new self(new PointsTransferId($data['id']), $data['value'], $createdAt, $data['canceled']);
        if (isset($data['comment'])) {
            $transfer->comment = $data['comment'];
        }
        if (isset($data['issuer'])) {
            $transfer->issuer = $data['issuer'];
        }

        if (isset($data['transactionId'])) {
            $transfer->transactionId = new TransactionId($data['transactionId']);
        }

        if (isset($data['revisedTransactionId'])) {
            $transfer->transactionId = new TransactionId($data['revisedTransactionId']);
        }

        return $transfer;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return array_merge(
            parent::serialize(),
            [
                'transactionId' => $this->transactionId ? $this->transactionId->__toString() : null,
                'revisedTransactionId' => $this->revisedTransactionId ? $this->revisedTransactionId->__toString() : null,
            ]
        );
    }

    /**
     * @return TransactionId
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @return TransactionId
     */
    public function getRevisedTransactionId()
    {
        return $this->revisedTransactionId;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return self::TYPE_SYSTEM;
    }
}
