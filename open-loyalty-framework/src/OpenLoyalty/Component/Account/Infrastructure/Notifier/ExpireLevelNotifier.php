<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Account\Infrastructure\Notifier;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelRepository;
use OpenLoyalty\Component\Level\Infrastructure\Checker\LevelExpireCheckerInterface;
use OpenLoyalty\Component\Level\Infrastructure\Provider\NextLevelProviderInterface;
use OpenLoyalty\Component\Webhook\Domain\Command\DispatchWebhook;
use Psr\Log\LoggerInterface;

/**
 * Class ExpireLevelNotifier.
 */
class ExpireLevelNotifier implements ExpireLevelNotifierInterface
{
    private const REQUEST_PACKAGE_SIZE = 1000;

    private const NOTIFICATION_TYPE = 'account.expiring_level_notification';

    /**
     * @var int
     */
    private $sentNotifications = 0;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * @var NextLevelProviderInterface
     */
    private $nextLevelProvider;

    /**
     * @var LevelExpireCheckerInterface
     */
    private $levelExpireChecker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ExpireLevelNotifier constructor.
     *
     * @param CommandBus                  $commandBus
     * @param CustomerDetailsRepository   $customerDetailsRepository
     * @param LevelRepository             $levelRepository
     * @param NextLevelProviderInterface  $nextLevelProvider
     * @param LevelExpireCheckerInterface $levelExpireChecker
     * @param LoggerInterface             $logger
     */
    public function __construct(
        CommandBus $commandBus,
        CustomerDetailsRepository $customerDetailsRepository,
        LevelRepository $levelRepository,
        NextLevelProviderInterface $nextLevelProvider,
        LevelExpireCheckerInterface $levelExpireChecker,
        LoggerInterface $logger
    ) {
        $this->commandBus = $commandBus;
        $this->customerDetailsRepository = $customerDetailsRepository;
        $this->levelRepository = $levelRepository;
        $this->nextLevelProvider = $nextLevelProvider;
        $this->levelExpireChecker = $levelExpireChecker;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function sendNotificationsForLevelsExpiringAt(\DateTimeInterface $dateTime): void
    {
        $notifications = [];

        $customers = $this->customerDetailsRepository->findAll();

        /** @var CustomerDetails $customerDetails */
        foreach ($customers as $customerDetails) {
            $customerId = $customerDetails->getCustomerId();

            $customerLevelDetails = $customerDetails->getLevel();

            if (null === $customerLevelDetails) {
                continue;
            }

            /** @var Level $customerCurrentLevel */
            $customerCurrentLevel = $this->levelRepository->byId($customerLevelDetails->getLevelId());

            if (null === $customerCurrentLevel) {
                continue;
            }

            $lastLevelRecalculationDate = $customerDetails->getLastLevelRecalculation() ?: $customerDetails->getCreatedAt();

            if (!$this->levelExpireChecker->checkLevelExpiryOnDate($customerCurrentLevel, $lastLevelRecalculationDate, $dateTime)) {
                continue;
            }

            $customerNextLevel = $this->nextLevelProvider->getNextLevelForCustomerId($customerId);

            if (null === $customerNextLevel) {
                continue;
            }

            $notifications[] = $this->buildNotificationBody($customerDetails, $customerCurrentLevel, $customerNextLevel);

            ++$this->sentNotifications;
        }

        if (0 === count($notifications)) {
            return;
        }

        $notificationPackages = array_chunk($notifications, self::REQUEST_PACKAGE_SIZE);

        $this->dispatchWebhookRequest($notificationPackages);
    }

    /**
     * {@inheritdoc}
     */
    public function sentNotificationsCount(): int
    {
        return $this->sentNotifications;
    }

    /**
     * @param array $notificationPackages
     */
    private function dispatchWebhookRequest(array $notificationPackages): void
    {
        foreach ($notificationPackages as $package) {
            try {
                $this->commandBus->dispatch(new DispatchWebhook(self::NOTIFICATION_TYPE, $package));
            } catch (\Exception $exception) {
                $this->logger->error(sprintf('Cannot dispatch webhook %s', self::NOTIFICATION_TYPE));
            }
        }
    }

    /**
     * @param CustomerDetails $customerDetails
     * @param Level           $currentLevel
     * @param Level           $nextLevel
     *
     * @return array
     */
    private function buildNotificationBody(
        CustomerDetails $customerDetails,
        Level $currentLevel,
        Level $nextLevel
    ): array {
        return [
            'customerId' => (string) $customerDetails->getCustomerId(),
            'customerEmail' => $customerDetails->getEmail(),
            'customerPhone' => $customerDetails->getPhone(),
            'customerLoyaltyCardNumber' => $customerDetails->getLoyaltyCardNumber(),
            'customerFirstName' => $customerDetails->getFirstName(),
            'customerLastName' => $customerDetails->getLastName(),
            'levelId' => (string) $currentLevel->getLevelId(),
            'levelName' => $currentLevel->getName(),
            'futureLevelId' => (string) $nextLevel->getLevelId(),
            'futureLevelName' => $nextLevel->getName(),
        ];
    }
}
