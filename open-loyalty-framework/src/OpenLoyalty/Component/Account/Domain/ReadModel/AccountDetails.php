<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\ReadModel;

use Assert\AssertionFailedException;
use Broadway\ReadModel\SerializableReadModel;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\P2PAddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\PointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\Core\Domain\ReadModel\Versionable;
use OpenLoyalty\Component\Core\Domain\ReadModel\VersionableReadModel;

/**
 * Class AccountDetails.
 */
class AccountDetails implements SerializableReadModel, VersionableReadModel
{
    use Versionable;

    /**
     * @var AccountId
     */
    protected $accountId;

    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var PointsTransfer[]
     */
    protected $transfers = [];

    /**
     * @var \DateTime|null
     */
    protected $pointsResetAt;

    /**
     * AccountDetails constructor.
     *
     * @param AccountId  $id
     * @param CustomerId $customerId
     */
    public function __construct(AccountId $id, CustomerId $customerId)
    {
        $this->accountId = $id;
        $this->customerId = $customerId;
    }

    /**
     * @param \DateTime|null $pointsResetAt
     */
    public function setPointsResetAt(?\DateTime $pointsResetAt): void
    {
        $this->pointsResetAt = $pointsResetAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getPointsResetAt(): ?\DateTime
    {
        return $this->pointsResetAt;
    }

    /**
     * @param array $data
     *
     * @return mixed The object instance
     *
     * @throws AssertionFailedException
     */
    public static function deserialize(array $data): self
    {
        $account = new self(new AccountId($data['accountId']), new CustomerId($data['customerId']));

        foreach ($data['transfers'] as $transfer) {
            $account->addPointsTransfer($transfer['type']::deserialize($transfer['data']));
        }

        if (isset($data['pointsResetAt'])) {
            $resetAt = new \DateTime();
            $resetAt->setTimestamp($data['pointsResetAt']);

            $account->setPointsResetAt($resetAt);
        }

        return $account;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        $transfers = [];
        foreach ($this->transfers as $transfer) {
            $transfers[] = [
                'type' => get_class($transfer),
                'data' => $transfer->serialize(),
            ];
        }

        return [
            'accountId' => (string) $this->accountId,
            'pointsResetAt' => $this->pointsResetAt ? $this->pointsResetAt->getTimestamp() : null,
            'customerId' => (string) $this->customerId,
            'transfers' => $transfers,
        ];
    }

    /**
     * @param PointsTransfer $pointsTransfer
     */
    public function addPointsTransfer(PointsTransfer $pointsTransfer): void
    {
        if (isset($this->transfers[(string) $pointsTransfer->getId()])) {
            throw new \InvalidArgumentException(sprintf('%s already exists', (string) $pointsTransfer->getId()));
        }

        $this->transfers[(string) $pointsTransfer->getId()] = $pointsTransfer;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->accountId;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
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
     * @return AccountId
     */
    public function getAccountId(): AccountId
    {
        return $this->accountId;
    }

    /**
     * @return AddPointsTransfer[]
     */
    public function getAllActiveAddPointsTransfers(): array
    {
        $transfers = [];
        foreach ($this->transfers as $pointsTransfer) {
            if (!$pointsTransfer instanceof AddPointsTransfer) {
                continue;
            }
            if ($pointsTransfer->isLocked() || $pointsTransfer->isExpired() || $pointsTransfer->getAvailableAmount() == 0 || $pointsTransfer->isCanceled()) {
                continue;
            }

            $transfers[] = $pointsTransfer;
        }

        usort($transfers, function (PointsTransfer $a, PointsTransfer $b) {
            return $a->getCreatedAt() > $b->getCreatedAt();
        });

        return $transfers;
    }

    /**
     * @return AddPointsTransfer[]
     */
    public function getAllActiveAndLockedAddPointsTransfers(): array
    {
        $transfers = [];
        foreach ($this->transfers as $pointsTransfer) {
            if (!$pointsTransfer instanceof AddPointsTransfer) {
                continue;
            }
            if ($pointsTransfer->isExpired() || $pointsTransfer->getAvailableAmount() == 0 || $pointsTransfer->isCanceled()) {
                continue;
            }

            $transfers[] = $pointsTransfer;
        }

        usort($transfers, function (PointsTransfer $a, PointsTransfer $b) {
            return $a->getCreatedAt() > $b->getCreatedAt();
        });

        return $transfers;
    }

    /**
     * @return AddPointsTransfer[]
     */
    public function getAllExpiredAddPointsTransfers(): array
    {
        $transfers = [];
        foreach ($this->transfers as $pointsTransfer) {
            if (!$pointsTransfer instanceof AddPointsTransfer) {
                continue;
            }
            if (!$pointsTransfer->isExpired()) {
                continue;
            }

            $transfers[$pointsTransfer->getCreatedAt()->getTimestamp().'_'.$pointsTransfer->getId()->__toString()] = $pointsTransfer;
        }

        ksort($transfers);

        return $transfers;
    }

    /**
     * @return AddPointsTransfer[]
     */
    public function getAllLockedAddPointsTransfers(): array
    {
        $transfers = [];
        foreach ($this->transfers as $pointsTransfer) {
            if (!$pointsTransfer instanceof AddPointsTransfer) {
                continue;
            }
            if (!$pointsTransfer->isLocked()) {
                continue;
            }

            $transfers[$pointsTransfer->getCreatedAt()->getTimestamp().'_'.$pointsTransfer->getId()->__toString()] = $pointsTransfer;
        }

        ksort($transfers);

        return $transfers;
    }

    /**
     * @return AddPointsTransfer[]
     */
    public function getAllAddPointsTransfers(): array
    {
        $transfers = [];
        foreach ($this->transfers as $pointsTransfer) {
            if (!$pointsTransfer instanceof AddPointsTransfer) {
                continue;
            }

            $transfers[$pointsTransfer->getCreatedAt()->getTimestamp().'_'.$pointsTransfer->getId()->__toString()] = $pointsTransfer;
        }

        ksort($transfers);

        return $transfers;
    }

    /**
     * @param PointsTransferId $pointsTransferId
     *
     * @return null|PointsTransfer
     */
    public function getTransfer(PointsTransferId $pointsTransferId): ?PointsTransfer
    {
        if (!isset($this->transfers[(string) $pointsTransferId])) {
            return null;
        }

        return $this->transfers[(string) $pointsTransferId];
    }

    /**
     * @param PointsTransfer $pointsTransfer
     */
    public function setTransfer(PointsTransfer $pointsTransfer): void
    {
        $this->transfers[(string) $pointsTransfer->getId()] = $pointsTransfer;
    }

    /**
     * @return float
     */
    public function getAvailableAmount(): float
    {
        $sum = 0.0;

        foreach ($this->getAllActiveAddPointsTransfers() as $pointsTransfer) {
            $sum += $pointsTransfer->getAvailableAmount();
        }

        return $sum;
    }

    /**
     * @return float
     */
    public function getP2PAvailableAmount(): float
    {
        $sum = 0.0;

        foreach ($this->getAllActiveAddPointsTransfers() as $pointsTransfer) {
            if (!$pointsTransfer instanceof P2PAddPointsTransfer) {
                continue;
            }

            $sum += $pointsTransfer->getAvailableAmount();
        }

        return $sum;
    }

    /**
     * @return float
     */
    public function getEarnedAmount(): float
    {
        $sum = 0.0;

        foreach ($this->getAllAddPointsTransfers() as $pointsTransfer) {
            if ($pointsTransfer->isCanceled() || $pointsTransfer instanceof P2PAddPointsTransfer) {
                continue;
            }

            $sum += $pointsTransfer->getValue();
        }

        return $sum;
    }

    /**
     * @param \DateTimeInterface $startDate
     *
     * @return float
     */
    public function getEarnedAmountSince(\DateTimeInterface $startDate): float
    {
        $sum = 0.0;

        foreach ($this->getAllAddPointsTransfers() as $pointsTransfer) {
            if ($pointsTransfer->isCanceled() || $pointsTransfer instanceof P2PAddPointsTransfer) {
                continue;
            }
            if ($pointsTransfer->getCreatedAt() <= $startDate) {
                continue;
            }
            $sum += $pointsTransfer->getValue();
        }

        return $sum;
    }

    /**
     * @return float
     */
    public function getUsedAmount(): float
    {
        $sum = 0.0;

        foreach ($this->getAllAddPointsTransfers() as $pointsTransfer) {
            $sum += $pointsTransfer->getUsedAmount();
        }

        return $sum;
    }

    /**
     * @return float
     */
    public function getExpiredAmount(): float
    {
        $sum = 0.0;

        foreach ($this->getAllExpiredAddPointsTransfers() as $pointsTransfer) {
            $sum += $pointsTransfer->getAvailableAmount();
        }

        return $sum;
    }

    /**
     * @return float
     */
    public function getLockedAmount(): float
    {
        $sum = 0.0;

        foreach ($this->getAllLockedAddPointsTransfers() as $pointsTransfer) {
            $sum += $pointsTransfer->getAvailableAmount();
        }

        return $sum;
    }

    /**
     * @return PointsTransfer[]
     */
    public function getTransfers(): array
    {
        return $this->transfers;
    }
}
