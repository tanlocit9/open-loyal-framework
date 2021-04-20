<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Notification\Transport;

use OpenLoyalty\Bundle\UserBundle\Service\EmailProvider;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetails;

/**
 * Class EmailNotificationTransport.
 */
class EmailNotificationTransport implements NotificationTransportInterface
{
    /**
     * @var EmailProvider
     */
    private $emailProvider;

    /**
     * EmailNotificationTransport constructor.
     *
     * @param EmailProvider $emailProvider
     */
    public function __construct(EmailProvider $emailProvider)
    {
        $this->emailProvider = $emailProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sendInvitation(InvitationDetails $invitation): void
    {
        if ($invitation->getRecipientEmail()) {
            $this->emailProvider->invitationEmail($invitation);
        }
    }

    /**
     * @param array $notification
     */
    public function sendRewardAvailableNotification(array $notification): void
    {
        // SKIP
    }
}
