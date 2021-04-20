<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\Provider;

use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;

/**
 * Class ParentTransactionIdProvider.
 */
class ParentTransactionIdProvider implements ParentTransactionIdProviderInterface
{
    /**
     * @var TransactionDetailsRepository
     */
    private $transactionDetailsRepository;

    /**
     * ParentTransactionIdProvider constructor.
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
    public function findParentTransactionId(string $childTransactionId): ?string
    {
        /** @var TransactionDetails $childTransaction */
        $childTransaction = $this->transactionDetailsRepository->find($childTransactionId);
        $parentTransaction = $this->transactionDetailsRepository->findTransactionByDocumentNumber($childTransaction->getRevisedDocument());

        return $parentTransaction ? $parentTransaction->getId() : null;
    }
}
