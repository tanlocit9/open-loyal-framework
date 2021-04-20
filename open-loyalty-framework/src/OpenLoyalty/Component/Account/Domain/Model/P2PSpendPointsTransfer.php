<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Model;

use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\TransactionId;

/**
 * Class P2PSpendPointsTransfer.
 */
class P2PSpendPointsTransfer extends SpendPointsTransfer
{
    /**
     * @var AccountId
     */
    private $receiverId;

    /**
     * P2PSpendPointsTransfer constructor.
     *
     * @param AccountId          $receiverId
     * @param PointsTransferId   $id
     * @param float              $value
     * @param \DateTime|null     $createdAt
     * @param bool               $canceled
     * @param null|string        $comment
     * @param string             $issuer
     * @param null|TransactionId $transactionId
     * @param null|TransactionId $revisedTransactionId
     */
    public function __construct(
        AccountId $receiverId,
        PointsTransferId $id,
        float $value,
        \DateTime $createdAt = null,
        bool $canceled = false,
        ?string $comment = null,
        string $issuer = self::ISSUER_SYSTEM,
        ?TransactionId $transactionId = null,
        ?TransactionId $revisedTransactionId = null
    ) {
        $this->receiverId = $receiverId;
        parent::__construct(
            $id,
            $value,
            $createdAt,
            $canceled,
            $comment,
            $issuer,
            $transactionId,
            $revisedTransactionId
        );
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

        $receiverId = new AccountId($data['receiverId']);

        $transfer = new self($receiverId, new PointsTransferId($data['id']), $data['value'], $createdAt, $data['canceled']);
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
        return array_merge(parent::serialize(), [
            'receiverId' => $this->receiverId->__toString(),
        ]);
    }

    /**
     * @return AccountId
     */
    public function getReceiverId(): AccountId
    {
        return $this->receiverId;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return self::TYPE_P2P;
    }
}
