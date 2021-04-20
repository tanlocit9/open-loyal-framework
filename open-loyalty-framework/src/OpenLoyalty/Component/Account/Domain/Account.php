<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain;

use OpenLoyalty\Component\Core\Domain\SnapableEventSourcedAggregateRoot;
use OpenLoyalty\Component\Account\Domain\Event\AccountWasCreated;
use OpenLoyalty\Component\Account\Domain\Event\PointsHasBeenReset;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenCanceled;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenExpired;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenUnlocked;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereAdded;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereSpent;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereTransferred;
use OpenLoyalty\Component\Account\Domain\Exception\PointsTransferCannotBeCanceledException;
use OpenLoyalty\Component\Account\Domain\Exception\NotEnoughPointsException;
use OpenLoyalty\Component\Account\Domain\Exception\PointsTransferAlreadyExistException;
use OpenLoyalty\Component\Account\Domain\Exception\PointsTransferCannotBeExpiredException;
use OpenLoyalty\Component\Account\Domain\Exception\PointsTransferCannotBeUnlockedException;
use OpenLoyalty\Component\Account\Domain\Exception\PointsTransferNotExistException;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\P2PAddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\P2PSpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\PointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\SpendPointsTransfer;

/**
 * Class Account.
 */
class Account extends SnapableEventSourcedAggregateRoot
{
    /**
     * @var AccountId
     */
    protected $id;

    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var PointsTransfer[]
     */
    protected $pointsTransfers = [];

    /**
     * @param PointsTransferId $pointsTransferId
     *
     * @return PointsTransfer|null
     */
    public function getTransferById(PointsTransferId $pointsTransferId): ?PointsTransfer
    {
        if (!isset($this->pointsTransfers[(string) $pointsTransferId])) {
            return null;
        }

        return $this->pointsTransfers[(string) $pointsTransferId];
    }

    /**
     * @param AccountId  $accountId
     * @param CustomerId $customerId
     *
     * @return Account
     */
    public static function createAccount(AccountId $accountId, CustomerId $customerId): Account
    {
        $account = new self();
        $account->create($accountId, $customerId);

        return $account;
    }

    /**
     * @param AddPointsTransfer $pointsTransfer
     */
    public function addPoints(AddPointsTransfer $pointsTransfer): void
    {
        $pointsWereAdded = new PointsWereAdded($this->id, $pointsTransfer);
        $this->apply($pointsWereAdded);
    }

    /**
     * @param SpendPointsTransfer $pointsTransfer
     */
    public function spendPoints(SpendPointsTransfer $pointsTransfer): void
    {
        if (!$pointsTransfer->getTransactionId() && $this->getAvailableAmount() < $pointsTransfer->getValue()) {
            throw new NotEnoughPointsException();
        }

        $pointsWereSpent = new PointsWereSpent($this->id, $pointsTransfer);
        $this->apply($pointsWereSpent);
    }

    /**
     * @param PointsTransferId $pointsTransferId
     */
    public function cancelPointsTransfer(PointsTransferId $pointsTransferId): void
    {
        $pointsTransferHasBeenCanceled = new PointsTransferHasBeenCanceled($this->id, $pointsTransferId);
        $this->apply($pointsTransferHasBeenCanceled);
    }

    /**
     * @param PointsTransferId $pointsTransferId
     */
    public function expirePointsTransfer(PointsTransferId $pointsTransferId): void
    {
        $pointsTransferHasBeenExpired = new PointsTransferHasBeenExpired($this->id, $pointsTransferId);
        $this->apply($pointsTransferHasBeenExpired);
    }

    /**
     * @param \DateTime $date
     */
    public function resetPoints(\DateTime $date): void
    {
        $pointsHasBeenReset = new PointsHasBeenReset($this->id, $date);
        $this->apply($pointsHasBeenReset);
    }

    /**
     * @param PointsTransferId $pointsTransferId
     */
    public function unlockPointsTransfer(PointsTransferId $pointsTransferId): void
    {
        $pointsTransferHasBeenUnlocked = new PointsTransferHasBeenUnlocked($this->id, $pointsTransferId);
        $this->apply($pointsTransferHasBeenUnlocked);
    }

    /**
     * @param P2PSpendPointsTransfer $pointsTransfer
     *
     * @return array
     *
     * @throws NotEnoughPointsException
     */
    public function transferPoints(P2PSpendPointsTransfer $pointsTransfer): array
    {
        if ($this->getAvailableAmount() < $pointsTransfer->getValue()) {
            throw new NotEnoughPointsException();
        }

        $transfers = [];
        $pointsToTransfer = $pointsTransfer->getValue();
        foreach ($this->getAllActiveAddPointsTransfers() as $transfer) {
            if ($pointsToTransfer <= 0) {
                break;
            }
            $availableAmount = $transfer->getAvailableAmount();
            if ($availableAmount > $pointsToTransfer) {
                $transfers[] = [$transfer, $pointsToTransfer];
                $pointsToTransfer = 0;
            } else {
                $pointsToTransfer -= $availableAmount;
                $transfers[] = [$transfer, $availableAmount];
            }
        }

        $pointsWereTransferred = new PointsWereTransferred($this->id, $pointsTransfer);
        $this->apply($pointsWereTransferred);

        return $transfers;
    }

    /**
     * @return string
     */
    public function getAggregateRootId(): string
    {
        return $this->id;
    }

    /**
     * @return AccountId
     */
    public function getId(): AccountId
    {
        return $this->id;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @param array $pointsTransfers
     */
    public function setPointsTransfers(array $pointsTransfers): void
    {
        $this->pointsTransfers = $pointsTransfers;
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
     * @param AccountWasCreated $event
     */
    protected function applyAccountWasCreated(AccountWasCreated $event): void
    {
        $this->id = $event->getAccountId();
        $this->customerId = $event->getCustomerId();
    }

    /**
     * @param PointsWereAdded $event
     */
    protected function applyPointsWereAdded(PointsWereAdded $event): void
    {
        $this->addPointsTransfer($event->getPointsTransfer());
    }

    /**
     * @param PointsWereSpent $event
     *
     * @throws \Assert\AssertionFailedException
     */
    protected function applyPointsWereSpent(PointsWereSpent $event): void
    {
        $this->addPointsTransfer($event->getPointsTransfer());
        $amount = $event->getPointsTransfer()->getValue();
        foreach ($this->getAllActiveAddPointsTransfers() as $pointsTransfer) {
            if ($amount <= 0) {
                break;
            }

            $availableAmount = $pointsTransfer->getAvailableAmount();

            if ($availableAmount > $amount) {
                $availableAmount -= $amount;
                $amount = 0;
            } else {
                $amount -= $availableAmount;
                $availableAmount = 0;
            }

            $this->pointsTransfers[(string) $pointsTransfer->getId()] = $pointsTransfer->updateAvailableAmount($availableAmount);
        }
    }

    /**
     * @param PointsWereTransferred $event
     *
     * @throws \Assert\AssertionFailedException
     */
    protected function applyPointsWereTransferred(PointsWereTransferred $event): void
    {
        $this->addPointsTransfer($event->getPointsTransfer());
        $amount = $event->getPointsTransfer()->getValue();
        foreach ($this->getAllActiveAddPointsTransfers() as $pointsTransfer) {
            if ($amount <= 0) {
                break;
            }

            $availableAmount = $pointsTransfer->getAvailableAmount();

            if ($availableAmount > $amount) {
                $availableAmount -= $amount;
                $amount = 0;
            } else {
                $amount -= $availableAmount;
                $availableAmount = 0;
            }

            $this->pointsTransfers[(string) $pointsTransfer->getId()] = $pointsTransfer->updateAvailableAmount($availableAmount);
        }
    }

    /**
     * @param PointsTransferHasBeenCanceled $event
     *
     * @throws PointsTransferCannotBeCanceledException
     */
    protected function applyPointsTransferHasBeenCanceled(PointsTransferHasBeenCanceled $event): void
    {
        $id = (string) $event->getPointsTransferId();
        if (!isset($this->pointsTransfers[$id])) {
            throw new PointsTransferNotExistException($id);
        }

        $transfer = $this->pointsTransfers[$id];
        if (!$transfer instanceof AddPointsTransfer || $transfer instanceof P2PAddPointsTransfer) {
            throw new PointsTransferCannotBeCanceledException($id);
        }

        $this->pointsTransfers[$id] = $transfer->cancel();
    }

    /**
     * @param PointsTransferHasBeenExpired $event
     */
    protected function applyPointsTransferHasBeenExpired(PointsTransferHasBeenExpired $event): void
    {
        $id = (string) $event->getPointsTransferId();
        if (!isset($this->pointsTransfers[$id])) {
            throw new PointsTransferNotExistException($id);
        }

        $transfer = $this->pointsTransfers[$id];
        if (!$transfer instanceof AddPointsTransfer && !$transfer instanceof P2PAddPointsTransfer) {
            throw new PointsTransferCannotBeExpiredException($id);
        }

        $this->pointsTransfers[$id] = $transfer->expire();
    }

    /**
     * @param PointsHasBeenReset $event
     */
    protected function applyPointsHasBeenReset(PointsHasBeenReset $event): void
    {
        $transfers = $this->getAllActiveAndLockedAddPointsTransfers();

        foreach ($transfers as $transfer) {
            $transfer->expire();
        }
    }

    /**
     * @param PointsTransferHasBeenUnlocked $event
     */
    protected function applyPointsTransferHasBeenUnlocked(PointsTransferHasBeenUnlocked $event): void
    {
        $id = (string) $event->getPointsTransferId();
        if (!isset($this->pointsTransfers[$id])) {
            throw new PointsTransferNotExistException($id);
        }

        $transfer = $this->pointsTransfers[$id];
        if (!$transfer instanceof AddPointsTransfer) {
            throw new PointsTransferCannotBeUnlockedException($id);
        }

        $transfer->unlock();
    }

    /**
     * @return AddPointsTransfer[]
     */
    protected function getAllActiveAddPointsTransfers(): array
    {
        $transfers = [];

        foreach ($this->pointsTransfers as $pointsTransfer) {
            if (!$pointsTransfer instanceof AddPointsTransfer) {
                continue;
            }

            if ($pointsTransfer->isLocked()) {
                continue;
            }

            if ($pointsTransfer->isExpired()) {
                continue;
            }

            if ($pointsTransfer->getAvailableAmount() == 0) {
                continue;
            }

            if ($pointsTransfer->isCanceled()) {
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
    protected function getAllActiveAndLockedAddPointsTransfers(): array
    {
        $transfers = [];

        foreach ($this->pointsTransfers as $pointsTransfer) {
            if (!$pointsTransfer instanceof AddPointsTransfer) {
                continue;
            }

            if ($pointsTransfer->isExpired()) {
                continue;
            }

            if ($pointsTransfer->getAvailableAmount() == 0) {
                continue;
            }

            if ($pointsTransfer->isCanceled()) {
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
     * @param $days
     *
     * @return AddPointsTransfer[]
     */
    protected function getAllNotExpiredAddPointsTransfersOlderThan($days): array
    {
        $transfers = [];

        $date = new \DateTime('-'.$days.' days');
        $date->setTime(0, 0, 0);

        foreach ($this->pointsTransfers as $pointsTransfer) {
            if (!$pointsTransfer instanceof AddPointsTransfer) {
                continue;
            }

            if ($pointsTransfer->isExpired()) {
                continue;
            }

            if ($pointsTransfer->isCanceled()) {
                continue;
            }

            if ($pointsTransfer->getCreatedAt() >= $date) {
                continue;
            }

            $transfers[] = $pointsTransfer;
        }

        return $transfers;
    }

    /**
     * @return AddPointsTransfer[]
     */
    protected function getAllNotLockedPointsTransfers(): array
    {
        $transfers = [];

        foreach ($this->pointsTransfers as $pointsTransfer) {
            if (!$pointsTransfer instanceof AddPointsTransfer) {
                continue;
            }

            if (!$pointsTransfer->isLocked()) {
                continue;
            }

            $transfers[] = $pointsTransfer;
        }

        return $transfers;
    }

    /**
     * @param PointsTransfer $pointsTransfer
     */
    private function addPointsTransfer(PointsTransfer $pointsTransfer): void
    {
        $pointsTransferId = (string) $pointsTransfer->getId();

        if (isset($this->pointsTransfers[$pointsTransferId])) {
            throw new PointsTransferAlreadyExistException($pointsTransferId);
        }

        $this->pointsTransfers[$pointsTransferId] = $pointsTransfer;
    }

    /**
     * @param AccountId  $accountId
     * @param CustomerId $customerId
     */
    private function create(AccountId $accountId, CustomerId $customerId)
    {
        $accountsWasCreated = new AccountWasCreated($accountId, $customerId);
        $this->apply($accountsWasCreated);
    }
}
