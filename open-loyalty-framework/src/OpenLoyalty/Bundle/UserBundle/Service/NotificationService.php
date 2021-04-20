<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Service;

use OpenLoyalty\Bundle\UserBundle\Notification\Transport\NotificationTransportInterface;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetails;

/**
 * Class NotificationService.
 */
class NotificationService implements NotificationServiceInterface
{
    /**
     * @var NotificationTransportInterface[]
     */
    private $transports = [];

    /**
     * {@inheritdoc}
     */
    public function addTransport(NotificationTransportInterface $transport): void
    {
        $this->transports[] = $transport;
    }

    /**
     * @return NotificationTransportInterface[]
     */
    protected function getAvailableTransports(): array
    {
        return array_filter($this->transports, function (NotificationTransportInterface $transport) {
            return $transport->isAvailable();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function sendInvitation(InvitationDetails $invitation): void
    {
        foreach ($this->getAvailableTransports() as $transport) {
            $transport->sendInvitation($invitation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendRewardAvailableNotification(array $notification): void
    {
        foreach ($this->getAvailableTransports() as $transport) {
            $transport->sendRewardAvailableNotification($notification);
        }
    }
}
