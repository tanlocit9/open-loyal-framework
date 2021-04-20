<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Infrastructure\SystemEvent\Listener;

use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerDeactivatedSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerLevelChangedSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerRegisteredSystemEvent;
use OpenLoyalty\Component\Webhook\Infrastructure\SystemEvent\Listener\BaseWebhookListener;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerUpdatedSystemEvent;

/**
 * Class WebhookListener.
 */
class WebhookListener extends BaseWebhookListener
{
    /**
     * Customer updated webhook type (CustomerSystemEvents::CUSTOMER_UPDATED).
     */
    const CUSTOMER_UPDATED_WEBHOOK_TYPE = 'customer.updated';

    /**
     * Customer registered webhook type (CustomerSystemEvents::CUSTOMER_REGISTERED).
     */
    const CUSTOMER_REGISTERED_WEBHOOK_TYPE = 'customer.registered';

    /**
     * Customer deactivated webhook type (CustomerSystemEvents::CUSTOMER_DEACTIVATED).
     */
    const CUSTOMER_DEACTIVATED_WEBHOOK_TYPE = 'customer.deactivated';

    /**
     * Customer level changed automatically webhook type (CustomerSystemEvents::CUSTOMER_LEVEL_CHANGED_AUTOMATICALLY).
     */
    const CUSTOMER_LEVEL_CHANGED_AUTOMATICALLY_WEBHOOK_TYPE = 'customer.level_changed_automatically';

    /**
     * Customer level changed webhook type (CustomerSystemEvents::CUSTOMER_LEVEL_CHANGED).
     */
    const CUSTOMER_LEVEL_CHANGED_WEBHOOK_TYPE = 'customer.level_changed';

    /**
     * @param CustomerUpdatedSystemEvent $event
     */
    public function onCustomerUpdated(CustomerUpdatedSystemEvent $event): void
    {
        $this->uniqueDispatchCommand(
            self::CUSTOMER_UPDATED_WEBHOOK_TYPE,
            ['customerId' => (string) $event->getCustomerId()]
        );
    }

    /**
     * @param CustomerRegisteredSystemEvent $event
     */
    public function onCustomerRegistered(CustomerRegisteredSystemEvent $event): void
    {
        $this->uniqueDispatchCommand(
            self::CUSTOMER_REGISTERED_WEBHOOK_TYPE,
            [
                'customerId' => (string) $event->getCustomerId(),
                'data' => $event->getCustomerData(),
            ]
        );
    }

    /**
     * @param CustomerDeactivatedSystemEvent $event
     */
    public function onCustomerDeactivated(CustomerDeactivatedSystemEvent $event): void
    {
        $this->uniqueDispatchCommand(
            self::CUSTOMER_DEACTIVATED_WEBHOOK_TYPE,
            [
                'customerId' => (string) $event->getCustomerId(),
            ]
        );
    }

    /**
     * @param CustomerLevelChangedSystemEvent $event
     */
    public function onCustomerLevelChangedAutomatically(CustomerLevelChangedSystemEvent $event): void
    {
        $this->uniqueDispatchCommand(
            self::CUSTOMER_LEVEL_CHANGED_AUTOMATICALLY_WEBHOOK_TYPE,
            [
                'customerId' => (string) $event->getCustomerId(),
                'levelId' => (string) $event->getLevelId(),
                'levelName' => $event->getLevelName(),
                'levelMove' => $event->getLevelMove(),
            ]
        );
    }

    /**
     * @param CustomerLevelChangedSystemEvent $event
     */
    public function onCustomerLevelChanged(CustomerLevelChangedSystemEvent $event): void
    {
        $this->uniqueDispatchCommand(
            self::CUSTOMER_LEVEL_CHANGED_WEBHOOK_TYPE,
            [
                'customerId' => (string) $event->getCustomerId(),
                'levelId' => (string) $event->getLevelId(),
                'levelName' => $event->getLevelName(),
            ]
        );
    }
}
