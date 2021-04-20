<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Service;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Bundle\SettingsBundle\Service\GeneralSettingsManager;
use OpenLoyalty\Bundle\SettingsBundle\Service\GeneralSettingsManagerInterface;
use OpenLoyalty\Component\Account\Domain\Command\ExpirePointsTransfer;
use OpenLoyalty\Component\Account\Domain\Command\UnlockPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\PointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetails;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetailsRepository;
use OpenLoyalty\Component\Account\Domain\TransactionId;
use OpenLoyalty\Component\Account\Infrastructure\PointsTransferManagerInterface;

/**
 * Class PointsTransfersManager.
 */
class PointsTransfersManager implements PointsTransferManagerInterface
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var PointsTransferDetailsRepository
     */
    protected $pointsTransferDetailsRepository;

    /**
     * @var GeneralSettingsManager
     */
    protected $settingsManager;

    /**
     * PointsTransfersManager constructor.
     *
     * @param CommandBus                      $commandBus
     * @param PointsTransferDetailsRepository $pointsTransferDetailsRepository
     * @param GeneralSettingsManagerInterface $settingsManager
     */
    public function __construct(
        CommandBus $commandBus,
        PointsTransferDetailsRepository $pointsTransferDetailsRepository,
        GeneralSettingsManagerInterface $settingsManager
    ) {
        $this->commandBus = $commandBus;
        $this->pointsTransferDetailsRepository = $pointsTransferDetailsRepository;
        $this->settingsManager = $settingsManager;
    }

    /**
     * @return array
     */
    public function expireTransfers()
    {
        $allTime = $this->settingsManager->getSettingByKey('allTimeActive');
        if (null !== $allTime && $allTime->getValue()) {
            return [];
        }

        $date = new \DateTime();
        $transfers = $this->pointsTransferDetailsRepository->findAllActiveAddingTransfersExpiredAfter($date);

        /** @var PointsTransferDetails $transfer */
        foreach ($transfers as $transfer) {
            $this->commandBus->dispatch(new ExpirePointsTransfer(
                $transfer->getAccountId(),
                $transfer->getPointsTransferId()
            ));
        }

        return $transfers;
    }

    /**
     * @param callable $transferUnlockedCallback
     *
     * @return array
     */
    public function unlockTransfers(callable $transferUnlockedCallback = null): array
    {
        $transfers = $this->pointsTransferDetailsRepository->findAllPendingAddingTransfersToUnlock(new \DateTime());

        /** @var PointsTransferDetails $transfer */
        foreach ($transfers as $transfer) {
            $this->commandBus->dispatch(new UnlockPointsTransfer(
                $transfer->getAccountId(),
                $transfer->getPointsTransferId()
            ));
            if (null !== $transferUnlockedCallback) {
                $transferUnlockedCallback($transfer);
            }
        }

        return $transfers;
    }

    /**
     * @return int
     */
    public function countTransfersToUnlock(): int
    {
        return count($this->pointsTransferDetailsRepository->findAllPendingAddingTransfersToUnlock(new \DateTime()));
    }

    /**
     * @param PointsTransferId   $id
     * @param int                $value
     * @param \DateTime|null     $createdAt
     * @param bool               $canceled
     * @param TransactionId|null $transactionId
     * @param string|null        $comment
     * @param string             $issuer
     *
     * @return AddPointsTransfer
     */
    public function createAddPointsTransferInstance(
        PointsTransferId $id,
        $value,
        \DateTime $createdAt = null,
        bool $canceled = false,
        TransactionId $transactionId = null,
        ?string $comment = null,
        $issuer = PointsTransfer::ISSUER_SYSTEM
    ): AddPointsTransfer {
        $validtyDaysDuration = $this->settingsManager->getPointsDaysActive();
        $lockDaysDuration = $this->settingsManager->getPointsDaysLocked();

        return new AddPointsTransfer(
            $id,
            $value,
            $validtyDaysDuration,
            $lockDaysDuration,
            $createdAt,
            $canceled,
            $transactionId,
            $comment,
            $issuer
        );
    }
}
