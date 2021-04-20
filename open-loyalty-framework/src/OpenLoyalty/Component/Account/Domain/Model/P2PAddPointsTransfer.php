<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Model;

use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\TransactionId;
use Assert\Assertion as Assert;

/**
 * Class P2PAddPointsTransfer.
 */
class P2PAddPointsTransfer extends AddPointsTransfer
{
    /**
     * @var AccountId
     */
    protected $senderId;

    /**
     * P2PAddPointsTransfer constructor.
     *
     * @param AccountId          $senderId
     * @param PointsTransferId   $id
     * @param float              $value
     * @param int|null           $validityDuration
     * @param int|null           $lockDaysDuration
     * @param \DateTime|null     $createdAt
     * @param bool               $canceled
     * @param TransactionId|null $transactionId
     * @param null|string        $comment
     * @param string             $issuer
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(
        AccountId $senderId,
        PointsTransferId $id,
        float $value,
        ?int $validityDuration = null,
        ?int $lockDaysDuration = null,
        \DateTime $createdAt = null,
        bool $canceled = false,
        TransactionId $transactionId = null,
        ?string $comment = null,
        string $issuer = self::ISSUER_SYSTEM
    ) {
        parent::__construct(
            $id,
            $value,
            $validityDuration,
            $lockDaysDuration,
            $createdAt,
            $canceled,
            $transactionId,
            $comment,
            $issuer
        );
        $this->senderId = $senderId;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return self::TYPE_P2P;
    }

    /**
     * @param PointsTransferId  $id
     * @param AccountId         $senderId
     * @param float             $points
     * @param AddPointsTransfer $transfer
     * @param \DateTime|null    $createdAt
     *
     * @return P2PAddPointsTransfer
     *
     * @throws \Assert\AssertionFailedException
     */
    public static function createFromAddPointsTransfer(PointsTransferId $id, AccountId $senderId, float $points, AddPointsTransfer $transfer, \DateTime $createdAt = null)
    {
        $self = new self(
            $senderId,
            $id,
            $points,
            null,
            null,
            $createdAt ?: new \DateTime(),
            false,
            null,
            null,
            PointsTransfer::ISSUER_API
        );
        $self->expiresAt = $transfer->getExpiresAt();

        return $self;
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

        $transfer = new self(
            new AccountId($data['senderId']),
            new PointsTransferId($data['id']),
            $data['value'],
            null,
            null,
            $createdAt,
            isset($data['canceled']) ? $data['canceled'] : false
        );

        if (isset($data['expiresAt'])) {
            $expiresAt = new \DateTime();
            $expiresAt->setTimestamp($data['expiresAt']);
            $transfer->expiresAt = $expiresAt;
        }

        if (isset($data['lockedUntil'])) {
            $lockedUntil = new \DateTime();
            $lockedUntil->setTimestamp($data['lockedUntil']);
            $transfer->lockUntil($lockedUntil);
        }

        $transfer->locked = isset($data['locked']) ? $data['locked'] : false;

        if (isset($data['availableAmount'])) {
            Assert::numeric($data['availableAmount']);
            Assert::min($data['availableAmount'], 0);
            $transfer->availableAmount = $data['availableAmount'];
        }
        if (isset($data['expired'])) {
            Assert::boolean($data['expired']);
            $transfer->expired = $data['expired'];
        }

        if (isset($data['transactionId'])) {
            $transfer->transactionId = new TransactionId($data['transactionId']);
        }

        if (isset($data['comment'])) {
            $transfer->comment = $data['comment'];
        }
        if (isset($data['issuer'])) {
            $transfer->issuer = $data['issuer'];
        }

        return $transfer;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return array_merge(parent::serialize(), [
            'senderId' => $this->senderId->__toString(),
        ]);
    }

    /**
     * @return AccountId
     */
    public function getSenderId(): AccountId
    {
        return $this->senderId;
    }
}
