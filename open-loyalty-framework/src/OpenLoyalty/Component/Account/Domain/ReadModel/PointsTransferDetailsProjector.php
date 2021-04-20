<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\ReadModel;

use Broadway\ReadModel\Repository;
use Broadway\Repository\Repository as AggregateRootRepository;
use OpenLoyalty\Component\Core\Infrastructure\Projector\Projector;
use OpenLoyalty\Component\Account\Domain\Account;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenCanceled;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenExpired;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenUnlocked;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereAdded;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereSpent;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereTransferred;
use OpenLoyalty\Component\Account\Domain\Model\P2PAddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosId;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use OpenLoyalty\Component\Transaction\Domain\Transaction;

/**
 * Class PointsTransferDetailsProjector.
 */
class PointsTransferDetailsProjector extends Projector
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var AggregateRootRepository
     */
    private $accountRepository;

    /**
     * @var AggregateRootRepository
     */
    private $customerRepository;

    /**
     * @var AggregateRootRepository
     */
    private $transactionRepository;

    /**
     * @var PosRepository
     */
    private $posRepository;

    /**
     * PointsTransferDetailsProjector constructor.
     *
     * @param Repository              $repository
     * @param AggregateRootRepository $accountRepository
     * @param AggregateRootRepository $customerRepository
     * @param AggregateRootRepository $transactionRepository
     * @param PosRepository           $posRepository
     */
    public function __construct(
        Repository $repository,
        AggregateRootRepository $accountRepository,
        AggregateRootRepository $customerRepository,
        AggregateRootRepository $transactionRepository,
        PosRepository $posRepository
    ) {
        $this->repository = $repository;
        $this->accountRepository = $accountRepository;
        $this->customerRepository = $customerRepository;
        $this->transactionRepository = $transactionRepository;
        $this->posRepository = $posRepository;
    }

    /**
     * @param PointsWereAdded $event
     */
    protected function applyPointsWereAdded(PointsWereAdded $event)
    {
        $transfer = $event->getPointsTransfer();
        $id = $transfer->getId();

        /** @var PointsTransferDetails $readModel */
        $readModel = $this->getReadModel($id, $event->getAccountId());
        $readModel->setValue($transfer->getValue());
        $readModel->setCreatedAt($transfer->getCreatedAt());
        $readModel->setExpiresAt($transfer->getExpiresAt());
        $readModel->setLockedUntil($transfer->getLockedUntil());
        $readModel->setTransactionId($transfer->getTransactionId());
        $readModel->setIssuer($transfer->getIssuer());
        $readModel->setComment($transfer->getComment());
        $readModel->setType(PointsTransferDetails::TYPE_ADDING);

        // set state
        if ($transfer->isCanceled()) {
            $state = PointsTransferDetails::STATE_CANCELED;
        } elseif ($transfer->isExpired()) {
            $state = PointsTransferDetails::STATE_EXPIRED;
        } elseif ($transfer->isLocked()) {
            $state = PointsTransferDetails::STATE_PENDING;
        } else {
            $state = PointsTransferDetails::STATE_ACTIVE;
        }
        $readModel->setState($state);

        // set type
        if ($transfer instanceof P2PAddPointsTransfer) {
            $readModel->setType(PointsTransferDetails::TYPE_P2P_ADDING);
            /** @var Account $account */
            $account = $this->accountRepository->load($transfer->getSenderId()->__toString());
            $readModel->setSenderId($account->getCustomerId());
        }

        // set posIdentifier
        if ($transfer->getTransactionId()) {
            /** @var Transaction $transaction */
            $transaction = $this->transactionRepository->load($transfer->getTransactionId()->__toString());
            if ($transaction->getPosId()) {
                $pos = $this->posRepository->byId(new PosId($transaction->getPosId()->__toString()));
                if ($pos instanceof Pos) {
                    $readModel->setPosIdentifier($pos->getIdentifier());
                }
            }
        }

        $this->repository->save($readModel);
    }

    /**
     * @param PointsWereSpent $event
     */
    protected function applyPointsWereSpent(PointsWereSpent $event)
    {
        $transfer = $event->getPointsTransfer();
        $id = $transfer->getId();

        /** @var PointsTransferDetails $readModel */
        $readModel = $this->getReadModel($id, $event->getAccountId());
        $readModel->setValue($transfer->getValue());
        $readModel->setCreatedAt($transfer->getCreatedAt());
        $readModel->setExpiresAt($transfer->getCreatedAt());
        $readModel->setState($transfer->isCanceled() ? PointsTransferDetails::STATE_CANCELED : PointsTransferDetails::STATE_ACTIVE);
        $readModel->setType(PointsTransferDetails::TYPE_SPENDING);
        $readModel->setComment($transfer->getComment());
        $readModel->setIssuer($transfer->getIssuer());
        $readModel->setTransactionId($transfer->getTransactionId());
        $readModel->setRevisedTransactionId($transfer->getRevisedTransactionId());

        if ($transfer->getTransactionId()) {
            /** @var Transaction $transaction */
            $transaction = $this->transactionRepository->load((string) $transfer->getTransactionId());
            if ($transaction->getPosId()) {
                $pos = $this->posRepository->byId(new PosId($transaction->getPosId()));
                if ($pos instanceof Pos) {
                    $readModel->setPosIdentifier($pos->getIdentifier());
                }
            }
        }
        $this->repository->save($readModel);
    }

    /**
     * @param PointsWereTransferred $event
     */
    protected function applyPointsWereTransferred(PointsWereTransferred $event)
    {
        $transfer = $event->getPointsTransfer();
        $id = $transfer->getId();
        /** @var PointsTransferDetails $readModel */
        $readModel = $this->getReadModel($id, $event->getAccountId());
        $readModel->setValue($transfer->getValue());
        $readModel->setCreatedAt($transfer->getCreatedAt());
        $readModel->setExpiresAt($transfer->getCreatedAt());
        $readModel->setState($transfer->isCanceled() ? PointsTransferDetails::STATE_CANCELED : PointsTransferDetails::STATE_ACTIVE);
        $readModel->setType(PointsTransferDetails::TYPE_P2P_SPENDING);
        $readModel->setComment($transfer->getComment());
        $readModel->setIssuer($transfer->getIssuer());
        $readModel->setTransactionId($transfer->getTransactionId());
        $readModel->setRevisedTransactionId($transfer->getRevisedTransactionId());

        /** @var Account $account */
        $account = $this->accountRepository->load((string) $transfer->getReceiverId());
        $readModel->setReceiverId($account->getCustomerId());

        if ($transfer->getTransactionId()) {
            /** @var Transaction $transaction */
            $transaction = $this->transactionRepository->load((string) $transfer->getTransactionId());
            if ($transaction->getPosId()) {
                $pos = $this->posRepository->byId(new PosId((string) $transaction->getPosId()));
                if ($pos instanceof Pos) {
                    $readModel->setPosIdentifier($pos->getIdentifier());
                }
            }
        }
        $this->repository->save($readModel);
    }

    /**
     * @param PointsTransferHasBeenCanceled $event
     */
    protected function applyPointsTransferHasBeenCanceled(PointsTransferHasBeenCanceled $event)
    {
        $id = $event->getPointsTransferId();
        /** @var PointsTransferDetails $readModel */
        $readModel = $this->getReadModel($id, $event->getAccountId());
        $readModel->setState(PointsTransferDetails::STATE_CANCELED);
        $this->repository->save($readModel);
    }

    /**
     * @param PointsTransferHasBeenExpired $event
     */
    protected function applyPointsTransferHasBeenExpired(PointsTransferHasBeenExpired $event)
    {
        $id = $event->getPointsTransferId();
        /** @var PointsTransferDetails $readModel */
        $readModel = $this->getReadModel($id, $event->getAccountId());
        $readModel->setState(PointsTransferDetails::STATE_EXPIRED);
        $this->repository->save($readModel);
    }

    /**
     * @param PointsTransferHasBeenUnlocked $event
     */
    protected function applyPointsTransferHasBeenUnlocked(PointsTransferHasBeenUnlocked $event)
    {
        $id = $event->getPointsTransferId();
        /** @var PointsTransferDetails $readModel */
        $readModel = $this->getReadModel($id, $event->getAccountId());
        $readModel->setState(PointsTransferDetails::STATE_ACTIVE);
        $this->repository->save($readModel);
    }

    /**
     * @param PointsTransferId $pointsTransferId
     * @param AccountId        $accountId
     *
     * @return PointsTransferDetails
     */
    private function getReadModel(PointsTransferId $pointsTransferId, AccountId $accountId)
    {
        $readModel = $this->repository->find((string) $pointsTransferId);

        if (null === $readModel) {
            /** @var Account $account */
            $account = $this->accountRepository->load((string) $accountId);
            /** @var Customer $customer */
            $customer = $this->customerRepository->load((string) $account->getCustomerId());

            $readModel = new PointsTransferDetails($pointsTransferId, $account->getCustomerId(), $accountId);
            $readModel->setCustomerEmail($customer->getEmail());
            $readModel->setCustomerFirstName($customer->getFirstName());
            $readModel->setCustomerLastName($customer->getLastName());
            $readModel->setCustomerPhone($customer->getPhone());
            $readModel->setCustomerLoyaltyCardNumber($customer->getLoyaltyCardNumber());
        }

        return $readModel;
    }
}
