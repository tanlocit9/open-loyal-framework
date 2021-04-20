<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\ReadModel;

use Broadway\ReadModel\Repository;
use Broadway\Repository\Repository as AggregateRootRepository;
use OpenLoyalty\Component\Core\Infrastructure\Projector\Projector;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosId;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use OpenLoyalty\Component\Transaction\Domain\Event\CustomerWasAssignedToTransaction;
use OpenLoyalty\Component\Transaction\Domain\Event\LabelsWereAppendedToTransaction;
use OpenLoyalty\Component\Transaction\Domain\Event\LabelsWereUpdated;
use OpenLoyalty\Component\Transaction\Domain\Event\TransactionWasRegistered;
use OpenLoyalty\Component\Transaction\Domain\Model\CustomerBasicData;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class TransactionDetailsProjector.
 */
class TransactionDetailsProjector extends Projector
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var PosRepository
     */
    private $posRepository;

    /**
     * @var AggregateRootRepository
     */
    private $transactionRepository;

    /**
     * TransactionDetailsProjector constructor.
     *
     * @param Repository              $repository
     * @param PosRepository           $posRepository
     * @param AggregateRootRepository $transactionRepository
     */
    public function __construct(
        Repository $repository,
        PosRepository $posRepository,
        AggregateRootRepository $transactionRepository
    ) {
        $this->repository = $repository;
        $this->posRepository = $posRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param TransactionWasRegistered $event
     */
    protected function applyTransactionWasRegistered(TransactionWasRegistered $event): void
    {
        $readModel = $this->getReadModel($event->getTransactionId());

        $transactionData = $event->getTransactionData();
        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->load((string) $event->getTransactionId());

        $readModel->setDocumentType($transactionData['documentType']);
        $readModel->setDocumentNumber($transactionData['documentNumber']);
        $readModel->setPurchaseDate($transactionData['purchaseDate']);
        $readModel->setPurchasePlace($transactionData['purchasePlace']);
        $readModel->setCustomerData(CustomerBasicData::deserialize($event->getCustomerData()));
        $readModel->setItems($event->getItems());
        $readModel->setPosId($event->getPosId());
        $readModel->setExcludedDeliverySKUs($event->getExcludedDeliverySKUs());
        $readModel->setExcludedLevelSKUs($event->getExcludedLevelSKUs());
        $readModel->setExcludedLevelCategories($event->getExcludedLevelCategories());
        $readModel->setRevisedDocument($event->getRevisedDocument());
        $readModel->setLabels($event->getLabels());
        $readModel->setGrossValue($transaction->getGrossValue());

        if ($readModel->getPosId()) {
            /** @var Pos $pos */
            $pos = $this->posRepository->byId(new PosId((string) $readModel->getPosId()));
            if ($pos) {
                $pos->setTransactionsAmount($pos->getTransactionsAmount() + $transaction->getGrossValue());
                $pos->setTransactionsCount($pos->getTransactionsCount() + 1);
                $this->posRepository->save($pos);
            }
        }

        $this->repository->save($readModel);
    }

    /**
     * @param CustomerWasAssignedToTransaction $event
     */
    public function applyCustomerWasAssignedToTransaction(CustomerWasAssignedToTransaction $event): void
    {
        $readModel = $this->getReadModel($event->getTransactionId());
        $readModel->setCustomerId($event->getCustomerId());
        $customerData = $readModel->getCustomerData();
        $customerData->updateEmailAndPhone($event->getEmail(), $event->getPhone());
        $this->repository->save($readModel);
    }

    /**
     * @param LabelsWereAppendedToTransaction $event
     */
    public function applyLabelsWereAppendedToTransaction(LabelsWereAppendedToTransaction $event): void
    {
        $readModel = $this->getReadModel($event->getTransactionId());
        $readModel->appendLabels($event->getLabels());
        $this->repository->save($readModel);
    }

    /**
     * @param LabelsWereUpdated $event
     */
    public function applyLabelsWereUpdated(LabelsWereUpdated $event): void
    {
        $readModel = $this->getReadModel($event->getTransactionId());
        $readModel->setLabels($event->getLabels());
        $this->repository->save($readModel);
    }

    /**
     * @param TransactionId $transactionId
     *
     * @return TransactionDetails
     */
    private function getReadModel(TransactionId $transactionId): TransactionDetails
    {
        /** @var TransactionDetails $readModel */
        $readModel = $this->repository->find((string) $transactionId);

        if (null === $readModel) {
            $readModel = new TransactionDetails($transactionId);
        }

        return $readModel;
    }
}
