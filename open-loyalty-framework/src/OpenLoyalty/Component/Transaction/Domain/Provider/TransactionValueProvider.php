<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\Provider;

use Broadway\Repository\Repository;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class TransactionValueProvider.
 */
class TransactionValueProvider implements TransactionValueProviderInterface
{
    /**
     * @var TransactionDetailsRepository
     */
    private $transactionDetailsRepository;

    /**
     * @var Repository
     */
    private $transactionRepository;

    /**
     * TransactionValueProvider constructor.
     *
     * @param TransactionDetailsRepository $transactionDetailsRepository
     * @param Repository                   $transactionRepository
     */
    public function __construct(TransactionDetailsRepository $transactionDetailsRepository, Repository $transactionRepository)
    {
        $this->transactionDetailsRepository = $transactionDetailsRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionValue(TransactionId $transactionId, bool $includeReturns = false): ?float
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->load((string) $transactionId);

        $transactionValue = $transaction ? $transaction->getGrossValue() : null;

        if ($includeReturns) {
            $returns = $this->transactionDetailsRepository->findReturnsByDocumentNumber($transaction->getDocumentNumber());
            foreach ($returns as $return) {
                /** @var Transaction $returnTransaction */
                $returnTransaction = $this->transactionRepository->load($return->getId());
                $transactionValue -= $returnTransaction->getGrossValue();
            }
        }

        return $transactionValue;
    }
}
