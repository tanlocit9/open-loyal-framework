<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\Event\Listener;

use Broadway\CommandHandling\CommandBus;
use Broadway\Domain\DomainMessage;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\EventHandling\EventListener;
use Broadway\Repository\Repository;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\CustomerRepository;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerSystemEvents;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerUpdatedSystemEvent;
use OpenLoyalty\Component\Transaction\Domain\Command\AssignCustomerToTransaction;
use OpenLoyalty\Component\Transaction\Domain\CustomerId;
use OpenLoyalty\Component\Transaction\Domain\CustomerIdProvider;
use OpenLoyalty\Component\Transaction\Domain\Event\TransactionWasRegistered;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\CustomerAssignedToTransactionSystemEvent;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\CustomerFirstTransactionSystemEvent;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\TransactionSystemEvents;
use OpenLoyalty\Component\Customer\Domain\CustomerId as ClientId;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\Exception\InvalidTransactionReturnDocumentNumberException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AssignCustomerToTransactionListener.
 */
class AssignCustomerToTransactionListener implements EventListener
{
    /**
     * @var CustomerIdProvider
     */
    protected $customerIdProvider;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var Repository
     */
    protected $transactionRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var TransactionDetailsRepository
     */
    protected $transactionDetailsRepository;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * AssignCustomerToTransactionListener constructor.
     *
     * @param CustomerIdProvider           $customerIdProvider
     * @param CommandBus                   $commandBus
     * @param EventDispatcher              $eventDispatcher
     * @param Repository                   $transactionRepository
     * @param CustomerRepository           $customerRepository
     * @param TransactionDetailsRepository $transactionDetailsRepository
     * @param TranslatorInterface          $translator
     */
    public function __construct(
        CustomerIdProvider $customerIdProvider,
        CommandBus $commandBus,
        EventDispatcher $eventDispatcher,
        Repository $transactionRepository,
        CustomerRepository $customerRepository,
        TransactionDetailsRepository $transactionDetailsRepository,
        TranslatorInterface $translator
    ) {
        $this->customerIdProvider = $customerIdProvider;
        $this->commandBus = $commandBus;
        $this->eventDispatcher = $eventDispatcher;
        $this->transactionRepository = $transactionRepository;
        $this->customerRepository = $customerRepository;
        $this->transactionDetailsRepository = $transactionDetailsRepository;
        $this->translator = $translator;
    }

    /**
     * @param TransactionWasRegistered $event
     *
     * @throws InvalidTransactionReturnDocumentNumberException
     */
    public function onTransactionRegistered(TransactionWasRegistered $event)
    {
        $customerId = $this->customerIdProvider->getId($event->getCustomerData());
        if ($customerId) {
            /** @var Customer $customer */
            $customer = $this->customerRepository->load($customerId);
            $this->commandBus->dispatch(
                new AssignCustomerToTransaction(
                    $event->getTransactionId(),
                    new CustomerId($customerId),
                    $customer->getEmail(),
                    $customer->getPhone()
                )
            );

            $this->checkTransactionOwner($event, $customerId);

            /** @var Transaction $transaction */
            $transaction = $this->transactionRepository->load((string) $event->getTransactionId());
            $transactionsCount = $customer->getTransactionsCount();

            $this->eventDispatcher->dispatch(
                TransactionSystemEvents::CUSTOMER_ASSIGNED_TO_TRANSACTION,
                [new CustomerAssignedToTransactionSystemEvent(
                    $event->getTransactionId(),
                    new CustomerId($customerId),
                    $transaction->getGrossValue(),
                    $transaction->getGrossValueWithoutDeliveryCosts(),
                    $transaction->getDocumentNumber(),
                    $transaction->getAmountExcludedForLevel(),
                    $transactionsCount,
                    $transaction->getDocumentType() == Transaction::TYPE_RETURN,
                    $transaction->getRevisedDocument()
                )]
            );

            if ($transactionsCount == 0) {
                $this->eventDispatcher->dispatch(
                    TransactionSystemEvents::CUSTOMER_FIRST_TRANSACTION,
                    [
                        new CustomerFirstTransactionSystemEvent(
                            $event->getTransactionId(),
                            new CustomerId($customerId)
                        ),
                    ]
                );
            }
            $this->eventDispatcher->dispatch(
                CustomerSystemEvents::CUSTOMER_UPDATED,
                [new CustomerUpdatedSystemEvent(new ClientId($customerId))]
            );
        }
    }

    /**
     * @param TransactionWasRegistered $event
     * @param string                   $customerId
     *
     * @throws InvalidTransactionReturnDocumentNumberException
     */
    private function checkTransactionOwner(TransactionWasRegistered $event, string $customerId): void
    {
        if (null != $event->getRevisedDocument()) {
            $transaction = $this->transactionDetailsRepository
                ->findTransactionByDocumentNumber($event->getRevisedDocument());

            if (null != $transaction && $customerId != (string) $transaction->getCustomerId()) {
                throw new InvalidTransactionReturnDocumentNumberException(
                    $this->translator->trans('transaction.document_return_incorrect_owner')
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();

        if ($event instanceof TransactionWasRegistered) {
            $this->onTransactionRegistered($event);
        }
    }
}
