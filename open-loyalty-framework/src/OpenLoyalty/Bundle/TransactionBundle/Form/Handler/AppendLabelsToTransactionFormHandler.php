<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Form\Handler;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Bundle\TransactionBundle\Model\AppendLabels;
use OpenLoyalty\Component\Transaction\Domain\Command\AppendLabelsToTransaction;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class AppendLabelsToTransactionFormHandler.
 */
class AppendLabelsToTransactionFormHandler
{
    /**
     * @var TransactionDetailsRepository
     */
    private $transactionDetailsRepository;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * ManuallyAssignCustomerToTransactionFormHandler constructor.
     *
     * @param TransactionDetailsRepository $transactionDetailsRepository
     * @param CommandBus                   $commandBus
     * @param AuthorizationChecker         $authorizationChecker
     */
    public function __construct(
        TransactionDetailsRepository $transactionDetailsRepository,
        CommandBus $commandBus,
        AuthorizationChecker $authorizationChecker
    ) {
        $this->transactionDetailsRepository = $transactionDetailsRepository;
        $this->commandBus = $commandBus;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param FormInterface $form
     *
     * @return bool|TransactionId
     */
    public function onSuccess(FormInterface $form)
    {
        /** @var AppendLabels $data */
        $data = $form->getData();

        $documentNumber = $data->getTransactionDocumentNumber();

        $transactions = $this->transactionDetailsRepository->findBy(['documentNumberRaw' => $documentNumber]);
        if (0 === count($transactions)) {
            $form->get('transactionDocumentNumber')->addError(new FormError('No such transaction'));

            return false;
        }

        /** @var TransactionDetails $transaction */
        $transaction = reset($transactions);

        if (false === $this->authorizationChecker->isGranted('APPEND_LABELS_TO_TRANSACTION', $transaction)) {
            throw new AccessDeniedException();
        }

        $this->commandBus->dispatch(new AppendLabelsToTransaction(
            $transaction->getTransactionId(),
            $data->getLabels()
        ));

        return $transaction->getTransactionId();
    }
}
