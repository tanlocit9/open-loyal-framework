<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\Repository\Repository;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\TransactionRegisteredEvent;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\TransactionSystemEvents;
use OpenLoyalty\Component\Transaction\Domain\Transaction;

/**
 * Class TransactionCommandHandler.
 */
class TransactionCommandHandler extends SimpleCommandHandler
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * TransactionCommandHandler constructor.
     *
     * @param Repository      $repository
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(Repository $repository, EventDispatcher $eventDispatcher)
    {
        $this->repository = $repository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param RegisterTransaction $command
     */
    public function handleRegisterTransaction(RegisterTransaction $command)
    {
        $transaction = Transaction::createTransaction(
            $command->getTransactionId(),
            $command->getTransactionData(),
            $command->getCustomerData(),
            $command->getItems(),
            $command->getPosId(),
            $command->getExcludedDeliverySKUs(),
            $command->getExcludedLevelSKUs(),
            $command->getExcludedCategories(),
            $command->getRevisedDocument(),
            $command->getLabels()
        );

        $this->repository->save($transaction);

        $this->eventDispatcher->dispatch(
            TransactionSystemEvents::TRANSACTION_REGISTERED,
            [new TransactionRegisteredEvent(
                $command->getTransactionId(),
                $command->getTransactionData(),
                $command->getCustomerData(),
                $command->getItems(),
                $command->getPosId()
            )]
        );
    }

    /**
     * @param AppendLabelsToTransaction $command
     */
    public function handleAppendLabelsToTransaction(AppendLabelsToTransaction $command)
    {
        /** @var Transaction $transaction */
        $transaction = $this->repository->load($command->getTransactionId()->__toString());
        $transaction->appendLabels($command->getLabels());
        $this->repository->save($transaction);
    }

    /**
     * @param EditTransactionLabels $command
     */
    public function handleEditTransactionLabels(EditTransactionLabels $command)
    {
        /** @var Transaction $transaction */
        $transaction = $this->repository->load($command->getTransactionId()->__toString());
        $transaction->setLabels($command->getLabels());
        $this->repository->save($transaction);
    }

    /**
     * @param AssignCustomerToTransaction $command
     */
    public function handleAssignCustomerToTransaction(AssignCustomerToTransaction $command)
    {
        /** @var Transaction $transaction */
        $transaction = $this->repository->load((string) $command->getTransactionId());
        $transaction->assignCustomerToTransaction(
            $command->getCustomerId(),
            $command->getEmail(),
            $command->getPhone()
        );
        $this->repository->save($transaction);
    }
}
