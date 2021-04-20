<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Infrastructure\SystemEvent\Listener;

use Broadway\CommandHandling\CommandBus;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBoughtRepository;
use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\Command\ReturnCustomerCampaign;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\TransactionId;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\CustomerAssignedToTransactionSystemEvent;
use OpenLoyalty\Component\Transaction\Domain\Transaction;

/**
 * Class CreateCouponsReturnListener.
 */
class CreateCouponsReturnListener
{
    /**
     * @var TransactionDetailsRepository
     */
    private $transactionDetailsRepository;

    /**
     * @var CampaignBoughtRepository
     */
    private $campaignBoughtRepository;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var CampaignRepository
     */
    private $campaignRepository;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * CreateCouponsReturnListener constructor.
     *
     * @param TransactionDetailsRepository $transactionDetailsRepository
     * @param CampaignBoughtRepository     $campaignBoughtRepository
     * @param CommandBus                   $commandBus
     * @param CampaignRepository           $campaignRepository
     * @param UuidGeneratorInterface       $uuidGenerator
     */
    public function __construct(
        TransactionDetailsRepository $transactionDetailsRepository,
        CampaignBoughtRepository $campaignBoughtRepository,
        CommandBus $commandBus,
        CampaignRepository $campaignRepository,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->transactionDetailsRepository = $transactionDetailsRepository;
        $this->campaignBoughtRepository = $campaignBoughtRepository;
        $this->commandBus = $commandBus;
        $this->campaignRepository = $campaignRepository;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @param CustomerAssignedToTransactionSystemEvent $event
     */
    public function handleCustomerAssignedToTransaction(CustomerAssignedToTransactionSystemEvent $event)
    {
        $transaction = $this->transactionDetailsRepository->find($event->getTransactionId());
        if (!$transaction instanceof TransactionDetails || $transaction->getDocumentType() !== Transaction::TYPE_RETURN) {
            return;
        }

        $revisedTransaction = null;
        if ($transaction->getRevisedDocument() && $transaction->getDocumentType() == Transaction::TYPE_RETURN) {
            $transactions = $this->transactionDetailsRepository->findBy(['documentNumberRaw' => $transaction->getRevisedDocument()]);
            if (count($transactions) > 0) {
                $revisedTransaction = reset($transactions);
            }
        }

        if (!$revisedTransaction instanceof TransactionDetails) {
            return;
        }

        $revisedAmount = abs($revisedTransaction->getGrossValue());
        $returnedAmount = abs($transaction->getGrossValue());

        $boughCampaigns = $this->campaignBoughtRepository->findByCustomerIdAndUsedForTransactionId(
            (string) $event->getCustomerId(),
            (string) $revisedTransaction->getId(),
            Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE
        );

        if (count($boughCampaigns) == 0) {
            return;
        }

        $purchases = $this->sortPurchases($boughCampaigns);
        $totalDiscount = array_reduce($purchases, function (float $amount, CampaignBought $bought) {
            $amount += (int) $bought->getCoupon()->getCode();

            return $amount;
        }, 0);

        $amountToReturn = round(($returnedAmount / $revisedAmount) * $totalDiscount, 2);

        if ($returnedAmount + $this->getReturnedAmount($transaction->getRevisedDocument()) >= $revisedAmount) {
            $this->returnAllCoupons($transaction->getId(), $purchases);
        } else {
            $this->generateCoupons($transaction->getId(), $purchases, $amountToReturn);
        }
    }

    /**
     * @param string $transactionId
     * @param array  $purchases
     */
    private function returnAllCoupons(string $transactionId, array $purchases): void
    {
        foreach ($purchases as $purchase) {
            if ($purchase->getReturnedAmount() >= (float) $purchase->getCoupon()->getCode()) {
                continue;
            }
            $availableAmount = (float) $purchase->getCoupon()->getCode() - $purchase->getReturnedAmount();

            $campaign = $this->campaignRepository->byId($purchase->getCampaignId());
            $this->commandBus->dispatch(
                new ReturnCustomerCampaign(
                    new CustomerId((string) $purchase->getCustomerId()),
                    new CampaignId((string) $purchase->getCampaignId()),
                    $campaign->getName(),
                    0,
                    new Coupon(
                        $this->uuidGenerator->generate(),
                        (string) $availableAmount
                    ),
                    $campaign->getReward(),
                    new TransactionId($transactionId),
                    $purchase->getId(),
                    $purchase->getStatus(),
                    $purchase->getActiveSince(),
                    $purchase->getActiveTo()
                )
            );
        }
    }

    /**
     * @param string           $transactionId
     * @param CampaignBought[] $purchases
     * @param float            $amountToReturn
     */
    private function generateCoupons(string $transactionId, array $purchases, float $amountToReturn): void
    {
        foreach ($purchases as $purchase) {
            if (0 == $amountToReturn) {
                return;
            }
            if ($purchase->getReturnedAmount() >= (float) $purchase->getCoupon()->getCode()) {
                continue;
            }
            $availableAmount = (float) $purchase->getCoupon()->getCode() - $purchase->getReturnedAmount();
            $couponValue = min($availableAmount, $amountToReturn);

            if (0 == $couponValue) {
                continue;
            }
            $campaign = $this->campaignRepository->byId($purchase->getCampaignId());
            $this->commandBus->dispatch(
                new ReturnCustomerCampaign(
                    new CustomerId((string) $purchase->getCustomerId()),
                    new CampaignId((string) $purchase->getCampaignId()),
                    $campaign->getName(),
                    0,
                    new Coupon(
                        $this->uuidGenerator->generate(),
                        (string) $couponValue
                    ),
                    $campaign->getReward(),
                    new TransactionId($transactionId),
                    $purchase->getId(),
                    $purchase->getStatus(),
                    $purchase->getActiveSince(),
                    $purchase->getActiveTo()
                )
            );

            $amountToReturn = $couponValue == $amountToReturn ? 0 : $amountToReturn - $couponValue;
        }
    }

    /**
     * @param string $revisedDocumentNumber
     *
     * @return float
     */
    private function getReturnedAmount(string $revisedDocumentNumber): float
    {
        $transactions = $this->transactionDetailsRepository->findReturnsByDocumentNumber($revisedDocumentNumber);

        return array_reduce($transactions, function (float $carry, TransactionDetails $transaction): float {
            $carry += abs($transaction->getGrossValue());

            return $carry;
        }, 0.0);
    }

    /**
     * @param CampaignBought[] $purchases
     *
     * @return array
     */
    private function sortPurchases(array $purchases): array
    {
        usort($purchases, function (CampaignBought $a, CampaignBought $b): int {
            if ($a->getActiveTo() > $b->getActiveTo()) {
                return -1;
            }

            if ($a->getActiveTo() == $b->getActiveTo()) {
                return (float) $a->getCoupon()->getCode() >= (float) $b->getCoupon()->getCode() ? 1 : -1;
            }

            return 1;
        });

        return $purchases;
    }
}
