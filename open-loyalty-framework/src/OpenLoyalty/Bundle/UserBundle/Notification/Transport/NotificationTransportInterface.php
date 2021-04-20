<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Notification\Transport;

use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetails;

/**
 * Interface NotificationTransportInterface.
 */
interface NotificationTransportInterface
{
    /**
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * @param InvitationDetails $invitation
     */
    public function sendInvitation(InvitationDetails $invitation): void;

    /**
     * @param array $notification
     */
    public function sendRewardAvailableNotification(array $notification): void;
}
