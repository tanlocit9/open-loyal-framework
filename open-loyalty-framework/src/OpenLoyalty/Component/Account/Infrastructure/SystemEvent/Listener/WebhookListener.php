<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Infrastructure\SystemEvent\Listener;

use OpenLoyalty\Component\Account\Domain\SystemEvent\AvailablePointsAmountChangedSystemEvent;
use OpenLoyalty\Component\Webhook\Infrastructure\SystemEvent\Listener\BaseWebhookListener;

/**
 * Class WebhookListener.
 */
class WebhookListener extends BaseWebhookListener
{
    /**
     * Available points changed webhook type (AccountSystemEvents::AVAILABLE_POINTS_AMOUNT_CHANGED).
     */
    const ACCOUNT_AVAILABLE_POINTS_AMOUNT_CHANGED_WEBHOOK_TYPE = 'account.available_points_amount_changed';

    /**
     * @param AvailablePointsAmountChangedSystemEvent $event
     */
    public function onAccountAvailablePointsAmountChanged(AvailablePointsAmountChangedSystemEvent $event): void
    {
        $this->uniqueDispatchCommand(
            self::ACCOUNT_AVAILABLE_POINTS_AMOUNT_CHANGED_WEBHOOK_TYPE,
            [
                'customerId' => $event->getCustomerId()->__toString(),
                'amount' => $event->getCurrentAmount(),
                'amount_change' => $event->getAmountChange(),
                'amount_change_type' => $event->getOperationType(),
            ]
        );
    }
}
