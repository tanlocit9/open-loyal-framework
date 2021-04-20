<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\ReadModel;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Core\Infrastructure\Projector\Projector;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Event\AccountWasCreated;
use OpenLoyalty\Component\Account\Domain\Event\PointsHasBeenReset;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenCanceled;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenExpired;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenUnlocked;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereAdded;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereSpent;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereTransferred;
use OpenLoyalty\Component\Account\Domain\Exception\PointsTransferCannotBeCanceledException;
use OpenLoyalty\Component\Account\Domain\Exception\PointsTransferCannotBeExpiredException;
use OpenLoyalty\Component\Account\Domain\Exception\PointsTransferCannotBeUnlockedException;
use OpenLoyalty\Component\Account\Domain\Exception\PointsTransferNotExistException;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\CustomerId;

/**
 * Class AccountDetailsProjector.
 */
class AccountDetailsProjector extends Projector
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * AccountDetailsProjector constructor.
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param AccountWasCreated $event
     */
    protected function applyAccountWasCreated(AccountWasCreated $event): void
    {
        $readModel = $this->getReadModel($event->getAccountId(), $event->getCustomerId());
        $this->repository->save($readModel);
    }

    /**
     * @param PointsWereAdded $event
     */
    protected function applyPointsWereAdded(PointsWereAdded $event): void
    {
        /** @var AccountDetails $readModel */
        $readModel = $this->getReadModel($event->getAccountId());
        $readModel->addPointsTransfer($event->getPointsTransfer());
        $this->repository->save($readModel);
    }

    /**
     * @param PointsWereSpent $event
     */
    protected function applyPointsWereSpent(PointsWereSpent $event): void
    {
        /** @var AccountDetails $readModel */
        $readModel = $this->getReadModel($event->getAccountId());
        $readModel->addPointsTransfer($event->getPointsTransfer());
        $amount = $event->getPointsTransfer()->getValue();
        foreach ($readModel->getAllActiveAddPointsTransfers() as $pointsTransfer) {
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
            $readModel->setTransfer($pointsTransfer->updateAvailableAmount($availableAmount));
        }
        $this->repository->save($readModel);
    }

    /**
     * @param PointsTransferHasBeenCanceled $event
     */
    protected function applyPointsTransferHasBeenCanceled(PointsTransferHasBeenCanceled $event): void
    {
        /** @var AccountDetails $readModel */
        $readModel = $this->getReadModel($event->getAccountId());
        $id = $event->getPointsTransferId();
        $transfer = $readModel->getTransfer($id);
        if (!$transfer) {
            throw new PointsTransferNotExistException($id->__toString());
        }
        if (!$transfer instanceof AddPointsTransfer) {
            throw new PointsTransferCannotBeCanceledException($id->__toString());
        }
        $readModel->setTransfer($transfer->cancel());
        $this->repository->save($readModel);
    }

    /**
     * @param PointsWereTransferred $event
     */
    protected function applyPointsWereTransferred(PointsWereTransferred $event): void
    {
        /** @var AccountDetails $readModel */
        $readModel = $this->getReadModel($event->getAccountId());
        $readModel->addPointsTransfer($event->getPointsTransfer());
        $amount = $event->getPointsTransfer()->getValue();
        foreach ($readModel->getAllActiveAddPointsTransfers() as $pointsTransfer) {
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
            $readModel->setTransfer($pointsTransfer->updateAvailableAmount($availableAmount));
        }
        $this->repository->save($readModel);
    }

    /**
     * @param PointsTransferHasBeenExpired $event
     */
    protected function applyPointsTransferHasBeenExpired(PointsTransferHasBeenExpired $event): void
    {
        /** @var AccountDetails $readModel */
        $readModel = $this->getReadModel($event->getAccountId());
        $id = $event->getPointsTransferId();
        $transfer = $readModel->getTransfer($id);
        if (!$transfer) {
            throw new PointsTransferNotExistException($id->__toString());
        }
        if (!$transfer instanceof AddPointsTransfer) {
            throw new PointsTransferCannotBeExpiredException($id->__toString());
        }
        $readModel->setTransfer($transfer->expire());
        $this->repository->save($readModel);
    }

    /**
     * @param PointsTransferHasBeenUnlocked $event
     */
    protected function applyPointsTransferHasBeenUnlocked(PointsTransferHasBeenUnlocked $event): void
    {
        /** @var AccountDetails $readModel */
        $readModel = $this->getReadModel($event->getAccountId());
        $id = $event->getPointsTransferId();
        $transfer = $readModel->getTransfer($id);
        if (!$transfer) {
            throw new PointsTransferNotExistException($id->__toString());
        }
        if (!$transfer instanceof AddPointsTransfer) {
            throw new PointsTransferCannotBeUnlockedException($id->__toString());
        }
        $readModel->setTransfer($transfer->unlock());
        $this->repository->save($readModel);
    }

    /**
     * @param PointsHasBeenReset $event
     */
    protected function applyPointsHasBeenReset(PointsHasBeenReset $event): void
    {
        /** @var AccountDetails $readModel */
        $readModel = $this->getReadModel($event->getAccountId());
        $readModel->setPointsResetAt($event->getDate());
        $transfers = $readModel->getAllActiveAndLockedAddPointsTransfers();
        foreach ($transfers as $transfer) {
            $readModel->setTransfer($transfer->expire());
        }
        $this->repository->save($readModel);
    }

    /**
     * @param AccountId       $accountId
     * @param CustomerId|null $customerId
     *
     * @return AccountDetails
     */
    private function getReadModel(AccountId $accountId, CustomerId $customerId = null): AccountDetails
    {
        $readModel = $this->repository->find($accountId->__toString());

        if (null === $readModel && $customerId) {
            $readModel = new AccountDetails($accountId, $customerId);
        }

        return $readModel;
    }
}
