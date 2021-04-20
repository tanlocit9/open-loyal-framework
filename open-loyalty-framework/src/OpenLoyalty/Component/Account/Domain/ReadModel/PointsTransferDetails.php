<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\ReadModel;

use Assert\AssertionFailedException;
use Broadway\ReadModel\SerializableReadModel;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Model\PointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\Account\Domain\TransactionId;
use OpenLoyalty\Component\Core\Domain\ReadModel\Versionable;
use OpenLoyalty\Component\Core\Domain\ReadModel\VersionableReadModel;

/**
 * Class PointsTransferDetails.
 */
class PointsTransferDetails implements SerializableReadModel, VersionableReadModel
{
    const TYPE_ADDING = 'adding';
    const TYPE_SPENDING = 'spending';
    const TYPE_P2P_SPENDING = 'p2p_spending';
    const TYPE_P2P_ADDING = 'p2p_adding';
    const STATE_CANCELED = 'canceled';
    const STATE_ACTIVE = 'active';
    const STATE_EXPIRED = 'expired';
    const STATE_PENDING = 'pending';

    use Versionable;

    /**
     * @var PointsTransferId
     */
    protected $pointsTransferId;

    /**
     * @var AccountId
     */
    protected $accountId;

    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var string
     */
    protected $customerFirstName;

    /**
     * @var string
     */
    protected $customerLastName;

    /**
     * @var string
     */
    protected $customerLoyaltyCardNumber;

    /**
     * @var string
     */
    protected $customerEmail;

    /**
     * @var string
     */
    protected $customerPhone;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $expiresAt;

    /**
     * @var \DateTime|null
     */
    protected $lockedUntil;

    /**
     * @var float
     */
    protected $value = 0;

    /**
     * @var string
     */
    protected $state = self::STATE_ACTIVE;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var TransactionId
     */
    protected $transactionId;

    /**
     * @var TransactionId
     */
    protected $revisedTransactionId;

    /**
     * @var string
     */
    protected $posIdentifier;

    /**
     * @var string
     */
    protected $comment;

    protected $issuer = PointsTransfer::ISSUER_SYSTEM;

    /**
     * @var CustomerId
     */
    protected $senderId;

    /**
     * @var CustomerId
     */
    protected $receiverId;

    /**
     * PointsTransfer constructor.
     *
     * @param PointsTransferId $pointsTransferId
     * @param CustomerId       $customerId
     * @param AccountId        $accountId
     */
    public function __construct(
        PointsTransferId $pointsTransferId,
        CustomerId $customerId,
        AccountId $accountId
    ) {
        $this->pointsTransferId = $pointsTransferId;
        $this->customerId = $customerId;
        $this->accountId = $accountId;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->pointsTransferId;
    }

    /**
     * @return PointsTransferId
     */
    public function getPointsTransferId()
    {
        return $this->pointsTransferId;
    }

    /**
     * @param array $data
     *
     * @return mixed The object instance
     *
     * @throws AssertionFailedException
     */
    public static function deserialize(array $data)
    {
        $newTransfer = new self(new PointsTransferId($data['id']), new CustomerId($data['customerId']), new AccountId($data['accountId']));
        $newTransfer->customerFirstName = $data['customerFirstName'];
        $newTransfer->customerLastName = $data['customerLastName'];
        $newTransfer->customerPhone = $data['customerPhone'];
        $newTransfer->customerEmail = $data['customerEmail'];
        $newTransfer->customerLoyaltyCardNumber = $data['customerLoyaltyCardNumber'];
        $newTransfer->value = $data['value'];
        $newTransfer->state = $data['state'];
        $newTransfer->type = $data['type'];
        $newTransfer->senderId = isset($data['senderId']) ? new CustomerId($data['senderId']) : null;
        $newTransfer->receiverId = isset($data['receiverId']) ? new CustomerId($data['receiverId']) : null;

        if (isset($data['posIdentifier'])) {
            $newTransfer->posIdentifier = $data['posIdentifier'];
        }
        $createdAt = new \DateTime();
        $createdAt->setTimestamp($data['createdAt']);
        $newTransfer->createdAt = $createdAt;

        $expiresAt = new \DateTime();
        $expiresAt->setTimestamp($data['expiresAt']);
        $newTransfer->expiresAt = $expiresAt;

        if (isset($data['lockedUntil'])) {
            $lockedUntil = new \DateTime();
            $lockedUntil->setTimestamp($data['lockedUntil']);
            $newTransfer->lockedUntil = $lockedUntil;
        } else {
            $newTransfer->lockedUntil = null;
        }

        if (isset($data['transactionId'])) {
            $newTransfer->transactionId = new TransactionId($data['transactionId']);
        }
        if (isset($data['revisedTransactionId'])) {
            $newTransfer->revisedTransactionId = new TransactionId($data['revisedTransactionId']);
        }
        if (isset($data['comment'])) {
            $newTransfer->comment = $data['comment'];
        }
        if (isset($data['issuer'])) {
            $newTransfer->issuer = $data['issuer'];
        }

        return $newTransfer;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        $data = [
            'id' => (string) $this->pointsTransferId,
            'customerId' => (string) $this->customerId,
            'accountId' => (string) $this->accountId,
            'customerFirstName' => $this->customerFirstName,
            'customerLastName' => $this->customerLastName,
            'customerPhone' => $this->customerPhone,
            'customerLoyaltyCardNumber' => $this->customerLoyaltyCardNumber,
            'customerEmail' => $this->customerEmail,
            'value' => $this->value,
            'type' => $this->type,
            'createdAt' => $this->createdAt->getTimestamp(),
            'expiresAt' => null !== $this->expiresAt ? $this->expiresAt->getTimestamp() : null,
            'lockedUntil' => null !== $this->lockedUntil ? $this->lockedUntil->getTimestamp() : null,
            'state' => $this->state,
            'transactionId' => $this->transactionId ? (string) $this->transactionId : null,
            'revisedTransactionId' => $this->revisedTransactionId ? (string) $this->revisedTransactionId : null,
            'comment' => $this->comment,
            'posIdentifier' => $this->posIdentifier,
            'issuer' => $this->issuer,
            'senderId' => $this->senderId ? (string) $this->senderId : null,
            'receiverId' => $this->receiverId ? (string) $this->receiverId : null,
        ];

        return $data;
    }

    /**
     * @return AccountId
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getCustomerFirstName()
    {
        return $this->customerFirstName;
    }

    /**
     * @return string
     */
    public function getCustomerLastName()
    {
        return $this->customerLastName;
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    /**
     * @return string
     */
    public function getCustomerPhone()
    {
        return $this->customerPhone;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getLockedUntil(): ?\DateTime
    {
        return $this->lockedUntil;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return round($this->value, 2);
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $customerFirstName
     */
    public function setCustomerFirstName($customerFirstName): void
    {
        $this->customerFirstName = $customerFirstName;
    }

    /**
     * @param string $customerLastName
     */
    public function setCustomerLastName($customerLastName): void
    {
        $this->customerLastName = $customerLastName;
    }

    /**
     * @param string $customerEmail
     */
    public function setCustomerEmail($customerEmail): void
    {
        $this->customerEmail = $customerEmail;
    }

    /**
     * @param string $customerPhone
     */
    public function setCustomerPhone($customerPhone): void
    {
        $this->customerPhone = $customerPhone;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @param \DateTime $expiresAt
     */
    public function setExpiresAt($expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @param \DateTime|null $lockedUntil
     */
    public function setLockedUntil(?\DateTime $lockedUntil): void
    {
        $this->lockedUntil = $lockedUntil;
    }

    /**
     * @param float $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @param string $state
     */
    public function setState($state): void
    {
        $this->state = $state;
    }

    /**
     * @param string $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return TransactionId
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param TransactionId $transactionId
     */
    public function setTransactionId($transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getCustomerLoyaltyCardNumber()
    {
        return $this->customerLoyaltyCardNumber;
    }

    /**
     * @param string $customerLoyaltyCardNumber
     */
    public function setCustomerLoyaltyCardNumber($customerLoyaltyCardNumber): void
    {
        $this->customerLoyaltyCardNumber = $customerLoyaltyCardNumber;
    }

    /**
     * @return string
     */
    public function getPosIdentifier()
    {
        return $this->posIdentifier;
    }

    /**
     * @param string $posIdentifier
     */
    public function setPosIdentifier($posIdentifier): void
    {
        $this->posIdentifier = $posIdentifier;
    }

    /**
     * @return mixed
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @param mixed $issuer
     */
    public function setIssuer($issuer): void
    {
        $this->issuer = $issuer;
    }

    /**
     * @return TransactionId
     */
    public function getRevisedTransactionId()
    {
        return $this->revisedTransactionId;
    }

    /**
     * @param TransactionId $revisedTransactionId
     */
    public function setRevisedTransactionId($revisedTransactionId): void
    {
        $this->revisedTransactionId = $revisedTransactionId;
    }

    /**
     * @return CustomerId
     */
    public function getSenderId(): CustomerId
    {
        return $this->senderId;
    }

    /**
     * @return CustomerId
     */
    public function getReceiverId(): CustomerId
    {
        return $this->receiverId;
    }

    /**
     * @param CustomerId $senderId
     */
    public function setSenderId(CustomerId $senderId): void
    {
        $this->senderId = $senderId;
    }

    /**
     * @param CustomerId $receiverId
     */
    public function setReceiverId(CustomerId $receiverId): void
    {
        $this->receiverId = $receiverId;
    }
}
