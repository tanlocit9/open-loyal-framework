<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Infrastructure\Notifier;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Webhook\Domain\Command\DispatchWebhook;
use Psr\Log\LoggerInterface;

/**
 * Class ExpireCouponsNotifier.
 */
class ExpireCouponsNotifier implements ExpireCouponsNotifierInterface
{
    private const NOTIFICATION_TYPE = 'campaign.coupon_expiration';

    /**
     * @var int
     */
    private $sentNotifications = 0;

    /**
     * @var array
     */
    private $notifications = [];

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ExpireCouponsNotifier constructor.
     *
     * @param CommandBus                $commandBus
     * @param CustomerDetailsRepository $customerDetailsRepository
     * @param LoggerInterface           $logger
     */
    public function __construct(
        CommandBus $commandBus,
        CustomerDetailsRepository $customerDetailsRepository,
        LoggerInterface $logger
    ) {
        $this->commandBus = $commandBus;
        $this->customerDetailsRepository = $customerDetailsRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function sendNotificationsForCouponsExpiringAt(\DateTimeInterface $dateTime): void
    {
        $customers = $this->customerDetailsRepository->findCustomersWithPurchasesExpiringAt($dateTime);

        $activeDateFrom = new \DateTimeImmutable($dateTime->format('Y-m-d'));
        $activeDateTo = $activeDateFrom->add(new \DateInterval('P1D'));

        /** @var CustomerDetails $customer */
        foreach ($customers as $customer) {
            /** @var CampaignPurchase $campaignPurchase */
            foreach ($customer->getCampaignPurchases() as $campaignPurchase) {
                if (null === $campaignPurchase->getActiveTo()) {
                    continue;
                }

                if ($campaignPurchase->getActiveTo()->getTimestamp() < $activeDateFrom->getTimestamp()) {
                    continue;
                }

                if ($campaignPurchase->getActiveTo()->getTimestamp() > $activeDateTo->getTimestamp()) {
                    continue;
                }

                $this->notifications[] = [
                    'customerId' => (string) $customer->getCustomerId(),
                    'customerEmail' => $customer->getEmail(),
                    'customerLoyaltyCardNumber' => $customer->getLoyaltyCardNumber(),
                    'customerPhone' => $customer->getPhone(),
                    'coupon' => $campaignPurchase->getCoupon()->getCode(),
                    'couponExpiresAt' => $campaignPurchase->getActiveTo()->getTimestamp(),
                    'couponStatus' => $campaignPurchase->getStatus(),
                ];
            }
        }

        if (0 === count($this->notifications)) {
            return;
        }

        try {
            $this->commandBus->dispatch(new DispatchWebhook(self::NOTIFICATION_TYPE, $this->notifications));

            ++$this->sentNotifications;
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Cannot dispatch webhook %s', self::NOTIFICATION_TYPE));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sentNotificationsCount(): int
    {
        return $this->sentNotifications;
    }

    /**
     * {@inheritdoc}
     */
    public function notificationsCount(): int
    {
        return count($this->notifications);
    }
}
