<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Infrastructure\Notifier;

/**
 * Interface ExpireCouponsNotifierInterface.
 */
interface ExpireCouponsNotifierInterface
{
    /**
     * @param \DateTimeInterface $dateTime
     */
    public function sendNotificationsForCouponsExpiringAt(\DateTimeInterface $dateTime): void;

    /**
     * @return int
     */
    public function notificationsCount(): int;

    /**
     * @return int
     */
    public function sentNotificationsCount(): int;
}
