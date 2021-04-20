<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Bundle\TransactionBundle\Validator\Constraints;

use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class TransactionReturnDocumentValidator.
 */
class TransactionReturnDocumentValidator extends ConstraintValidator
{
    private const DOCUMENT_TYPE_RETURN = 'return';
    private const TRANSACTION_NOT_EXIST = 'Transaction not exist';
    private const TRANSACTION_WRONG_TYPE = 'Transaction wrong type';
    private const TRANSACTION_INCORRECT_OWNER = 'Incorrect owner of the transaction';

    /**
     * @var TransactionDetailsRepository
     */
    private $transactionDetailsRepository;

    /**
     * TransactionReturnDocumentValidator constructor.
     *
     * @param TransactionDetailsRepository $transactionDetailsRepository
     */
    public function __construct(TransactionDetailsRepository $transactionDetailsRepository)
    {
        $this->transactionDetailsRepository = $transactionDetailsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TransactionReturnDocument) {
            return;
        }

        if (null === $value) {
            return;
        }

        $transaction = $this->transactionDetailsRepository->findTransactionByDocumentNumber($value);

        if ($this->isTransactionIsNull($transaction)) {
            return;
        }

        if (self::DOCUMENT_TYPE_RETURN === $transaction->getDocumentType() && $transaction->getRevisedDocument() != null) {
            $basedDocumentValue = $transaction->getRevisedDocument();

            if ($constraint->getIsManually()) {
                $basedDocumentValue = $transaction->getDocumentNumber();
            }

            $basedTransaction = $this->transactionDetailsRepository->findTransactionByDocumentNumber($basedDocumentValue);

            if ($this->isTransactionIsNull($basedTransaction)) {
                return;
            }

            if ($this->isReturnTransaction($transaction) && $this->isReturnTransaction($basedTransaction)) {
                $this->context->buildViolation(self::TRANSACTION_WRONG_TYPE)->addViolation();

                return;
            }

            if ($this->isOwnerOfBasedTransaction($constraint, $transaction, $basedTransaction)) {
                return;
            }
        }
    }

    /**
     * @param null|TransactionDetails $transaction
     *
     * @return bool
     */
    private function isTransactionIsNull(?TransactionDetails $transaction): bool
    {
        if (null === $transaction) {
            $this->context->buildViolation(self::TRANSACTION_NOT_EXIST)->addViolation();

            return true;
        }

        return false;
    }

    /**
     * @param TransactionDetails $transaction
     *
     * @return bool
     */
    private function isReturnTransaction(TransactionDetails $transaction): bool
    {
        if (self::DOCUMENT_TYPE_RETURN === $transaction->getDocumentType()) {
            return true;
        }

        return false;
    }

    /**
     * @param TransactionReturnDocument $constraint
     * @param TransactionDetails        $transaction
     * @param TransactionDetails        $basedTransaction
     *
     * @return bool
     */
    private function isOwnerOfBasedTransaction(
        TransactionReturnDocument $constraint,
        TransactionDetails $transaction,
        TransactionDetails $basedTransaction
    ): bool {
        if ($constraint->getIsManually()
            && null !== $transaction->getCustomerId()
            && null !== $basedTransaction->getCustomerId()
            && (string) $transaction->getCustomerId() !== (string) $basedTransaction->getCustomerId()
        ) {
            $this->context->buildViolation(self::TRANSACTION_INCORRECT_OWNER)->addViolation();

            return false;
        }

        return true;
    }
}
