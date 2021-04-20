<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Infrastructure\Event\Listener;

use Broadway\CommandHandling\CommandBus;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListener;
use Broadway\ReadModel\Repository;
use Broadway\Repository\Repository as AggregateRootRepository;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Component\Account\Domain\Command\SpendPoints;
use OpenLoyalty\Component\Account\Domain\Model\PointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\SpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetails;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetailsRepository;
use OpenLoyalty\Component\Account\Domain\TransactionId;
use OpenLoyalty\Component\Transaction\Domain\Event\CustomerWasAssignedToTransaction;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\Transaction;

/**
 * Class RevokePointsOnReturnTransactionListener.
 */
class RevokePointsOnReturnTransactionListener implements EventListener
{
    /**
     * @var AggregateRootRepository
     */
    private $transactionRepository;

    /**
     * @var TransactionDetailsRepository
     */
    private $transactionDetailsRepository;

    /**
     * @var PointsTransferDetailsRepository
     */
    private $transfersRepo;

    /**
     * @var Repository
     */
    private $accountDetailsRepository;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * RevokePointsOnReturnTransactionListener constructor.
     *
     * @param AggregateRootRepository         $transactionRepository
     * @param TransactionDetailsRepository    $transactionDetailsRepository
     * @param PointsTransferDetailsRepository $transfersRepo
     * @param Repository                      $accountDetailsRepository
     * @param CommandBus                      $commandBus
     * @param UuidGeneratorInterface          $uuidGenerator
     */
    public function __construct(
        AggregateRootRepository $transactionRepository,
        TransactionDetailsRepository $transactionDetailsRepository,
        PointsTransferDetailsRepository $transfersRepo,
        Repository $accountDetailsRepository,
        CommandBus $commandBus,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->transactionDetailsRepository = $transactionDetailsRepository;
        $this->transfersRepo = $transfersRepo;
        $this->accountDetailsRepository = $accountDetailsRepository;
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @param DomainMessage $domainMessage
     */
    public function handle(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();
        if ($event instanceof CustomerWasAssignedToTransaction) {
            $this->onCustomerWasAssignedToTransaction($event);
        }
    }

    /**
     * @param CustomerWasAssignedToTransaction $event
     */
    public function onCustomerWasAssignedToTransaction(CustomerWasAssignedToTransaction $event)
    {
        $transaction = $this->transactionRepository->load((string) $event->getTransactionId());
        if (!$transaction instanceof Transaction) {
            return;
        }

        $revisedTransaction = null;
        if ($transaction->getRevisedDocument() && $transaction->getDocumentType() == Transaction::TYPE_RETURN) {
            $revisedTransactionsDetails = $this->transactionDetailsRepository->findBy(['documentNumberRaw' => $transaction->getRevisedDocument()]);
            if (count($revisedTransactionsDetails) > 0) {
                $revisedTransactionDetails = reset($revisedTransactionsDetails);
                if ($revisedTransactionDetails instanceof TransactionDetails) {
                    $revisedTransaction = $this->transactionRepository->load($revisedTransactionDetails->getId());
                }
            }
        }

        if (!$revisedTransaction instanceof Transaction) {
            return;
        }

        $amount = abs($revisedTransaction->getGrossValue());
        $points = $this->getPointsForTransaction($revisedTransaction);

        $pointsToRevoke = round($points / $amount * abs($transaction->getGrossValue()), 2);

        $account = $this->getAccountDetails((string) $event->getCustomerId());
        if (!$account) {
            return;
        }

        if ($this->getAlreadyRevokedPoints($revisedTransaction) >= $points) {
            return;
        }

        $this->commandBus->dispatch(
            new SpendPoints($account->getAccountId(), new SpendPointsTransfer(
                new PointsTransferId($this->uuidGenerator->generate()),
                $pointsToRevoke,
                null,
                false,
                null,
                PointsTransfer::ISSUER_SYSTEM,
                new TransactionId($transaction->getId()),
                new TransactionId($revisedTransaction->getId())
            ))
        );
    }

    /**
     * @param Transaction $transaction
     *
     * @return float|null
     */
    private function getAlreadyRevokedPoints(Transaction $transaction): ?float
    {
        $transfers = $this->transfersRepo->findBy([
            'revisedTransactionId' => (string) $transaction->getTransactionId(),
            'state' => PointsTransferDetails::STATE_ACTIVE,
            'type' => PointsTransferDetails::TYPE_SPENDING,
        ]);

        return array_reduce($transfers, function ($carry, PointsTransferDetails $transfer) {
            $carry += $transfer->getValue();

            return $carry;
        });
    }

    /**
     * @param Transaction $transaction
     *
     * @return float|null
     */
    private function getPointsForTransaction(Transaction $transaction): ?float
    {
        $transfers = $this->transfersRepo->findBy([
            'transactionId' => (string) $transaction->getTransactionId(),
            'state' => PointsTransferDetails::STATE_ACTIVE,
            'type' => PointsTransferDetails::TYPE_ADDING,
        ]);

        return array_reduce($transfers, function ($carry, PointsTransferDetails $transfer) {
            $carry += $transfer->getValue();

            return $carry;
        });
    }

    /**
     * @param string $customerId
     *
     * @return AccountDetails|null
     */
    protected function getAccountDetails(string $customerId): ?AccountDetails
    {
        $accounts = $this->accountDetailsRepository->findBy(['customerId' => $customerId]);
        if (count($accounts) == 0) {
            return null;
        }
        /** @var AccountDetails $account */
        $account = reset($accounts);

        if (!$account instanceof AccountDetails) {
            return null;
        }

        return $account;
    }
}
