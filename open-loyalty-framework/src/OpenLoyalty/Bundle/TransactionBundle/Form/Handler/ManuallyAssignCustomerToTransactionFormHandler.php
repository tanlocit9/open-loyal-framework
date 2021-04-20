<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Form\Handler;

use Broadway\CommandHandling\CommandBus;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\Repository\Repository;
use OpenLoyalty\Bundle\TransactionBundle\Model\AssignCustomer;
use OpenLoyalty\Component\Customer\Domain\Exception\TooManyResultsException;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\Command\AssignCustomerToTransaction;
use OpenLoyalty\Component\Transaction\Domain\CustomerId;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\CustomerAssignedToTransactionSystemEvent;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\TransactionSystemEvents;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ManuallyAssignCustomerToTransactionFormHandler.
 */
class ManuallyAssignCustomerToTransactionFormHandler
{
    const DOCUMENT_TYPE_RETURN = 'return';

    /**
     * @var Repository
     */
    protected $transactionRepository;

    /**
     * @var TransactionDetailsRepository
     */
    protected $transactionDetailsRepository;

    /**
     * @var CustomerDetailsRepository
     */
    protected $customerDetailsRepository;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var AuthorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * ManuallyAssignCustomerToTransactionFormHandler constructor.
     *
     * @param Repository                   $transactionRepository
     * @param TransactionDetailsRepository $transactionDetailsRepository
     * @param CustomerDetailsRepository    $customerDetailsRepository
     * @param CommandBus                   $commandBus
     * @param EventDispatcher              $eventDispatcher
     * @param AuthorizationChecker         $authorizationChecker
     * @param TranslatorInterface          $translator
     */
    public function __construct(
        Repository $transactionRepository,
        TransactionDetailsRepository $transactionDetailsRepository,
        CustomerDetailsRepository $customerDetailsRepository,
        CommandBus $commandBus,
        EventDispatcher $eventDispatcher,
        AuthorizationChecker $authorizationChecker,
        TranslatorInterface $translator
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->transactionDetailsRepository = $transactionDetailsRepository;
        $this->customerDetailsRepository = $customerDetailsRepository;
        $this->commandBus = $commandBus;
        $this->eventDispatcher = $eventDispatcher;
        $this->authorizationChecker = $authorizationChecker;
        $this->translator = $translator;
    }

    /**
     * @param FormInterface $form
     *
     * @return bool|TransactionId
     */
    public function onSuccess(FormInterface $form)
    {
        /** @var AssignCustomer $data */
        $data = $form->getData();

        $documentNumber = $data->getTransactionDocumentNumber();

        $transactionsDetails = $this->transactionDetailsRepository->findBy(['documentNumberRaw' => $documentNumber]);
        if (count($transactionsDetails) == 0) {
            $form->get('transactionDocumentNumber')->addError(new FormError('No such transaction'));

            return false;
        }
        /** @var TransactionDetails $transactionDetail */
        $transactionDetail = reset($transactionsDetails);

        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->load($transactionDetail->getId());

        if (!$this->authorizationChecker->isGranted('ASSIGN_CUSTOMER_TO_TRANSACTION', $transaction)) {
            throw new AccessDeniedException();
        }

        if ($transaction->getCustomerId()) {
            $form->get('transactionDocumentNumber')->addError(new FormError('Customer is already assign to this transaction'));

            return false;
        }
        $criteria = null;
        $criteria = [];

        $field = 'loyaltyCardNumber';

        if ($data->getCustomerId()) {
            $criteria['id'] = $data->getCustomerId();
            $field = 'customerId';
        }
        if ($data->getCustomerLoyaltyCardNumber()) {
            $criteria['loyaltyCardNumber'] = strtolower($data->getCustomerLoyaltyCardNumber());
        }
        if ($data->getCustomerPhoneNumber()) {
            $criteria['phone'] = strtolower($data->getCustomerPhoneNumber());
            $field = 'customerPhoneNumber';
        }

        if (count($criteria) == 0) {
            throw new \InvalidArgumentException('One customer field is required');
        }

        try {
            $customers = $this->customerDetailsRepository->findOneByCriteria($criteria, 1);
        } catch (TooManyResultsException $e) {
            $form->get($field)->addError(new FormError($this->translator->trans('To many customers with such data. Please provide more details.')));

            return false;
        }

        $customer = reset($customers);
        if (!$customer instanceof CustomerDetails) {
            $form->get($field)->addError(new FormError($this->translator->trans('Such customer does not exist. Please provide more details.')));

            return false;
        }

        if ($transaction->getDocumentType() === self::DOCUMENT_TYPE_RETURN) {
            $basedTransaction = $this->findBasedTransaction($form, $transaction);
            $isOwnerResult = $this->isOwnerOfBasedTransaction($form, $customer, $basedTransaction);
            if (!$isOwnerResult) {
                return false;
            }
        }

        $this->commandBus->dispatch(
            new AssignCustomerToTransaction(
                $transaction->getTransactionId(),
                new CustomerId(
                    (string) $customer->getCustomerId()
                ),
                $customer->getEmail(),
                $customer->getPhone()
            )
        );

        $this->eventDispatcher->dispatch(
            TransactionSystemEvents::CUSTOMER_ASSIGNED_TO_TRANSACTION,
            [new CustomerAssignedToTransactionSystemEvent(
                $transaction->getTransactionId(),
                new CustomerId((string) $customer->getCustomerId()),
                $transaction->getGrossValue(),
                $transaction->getGrossValueWithoutDeliveryCosts(),
                $transaction->getDocumentNumber(),
                0,
                null,
                $transaction->getDocumentType() === Transaction::TYPE_RETURN,
                $transaction->getRevisedDocument()
            )]
        );

        return $transaction->getTransactionId();
    }

    /**
     * @param FormInterface $form
     * @param Transaction   $transaction
     *
     * @return null|Transaction
     */
    private function findBasedTransaction(FormInterface $form, Transaction $transaction)
    {
        $transactionsDetails = $this->transactionDetailsRepository->findBy(['documentNumberRaw' => $transaction->getRevisedDocument()]);
        if (count($transactionsDetails) == 0) {
            $form->get('transactionDocumentNumber')->addError(new FormError('No such transaction'));

            return null;
        }

        /** @var TransactionDetails $transactionDetail */
        $transactionDetail = reset($transactionsDetails);

        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->load($transactionDetail->getId());

        return $transaction;
    }

    /**
     * @param FormInterface   $form
     * @param CustomerDetails $customer
     * @param Transaction     $basedTransaction
     *
     * @return bool
     */
    private function isOwnerOfBasedTransaction(FormInterface $form, CustomerDetails $customer, Transaction $basedTransaction): bool
    {
        if ((string) $customer->getCustomerId() != (string) $basedTransaction->getCustomerId()) {
            $form->get('transactionDocumentNumber')->addError(new FormError($this->translator->trans('transaction.document_return_incorrect_owner')));

            return false;
        }

        return true;
    }
}
