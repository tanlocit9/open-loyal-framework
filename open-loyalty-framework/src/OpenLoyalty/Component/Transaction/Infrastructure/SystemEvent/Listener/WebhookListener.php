<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Infrastructure\SystemEvent\Listener;

use OpenLoyalty\Component\Transaction\Domain\SystemEvent\CustomerAssignedToTransactionSystemEvent;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\TransactionRegisteredEvent;
use OpenLoyalty\Component\Webhook\Infrastructure\SystemEvent\Listener\BaseWebhookListener;

/**
 * Class WebhookListener.
 */
class WebhookListener extends BaseWebhookListener
{
    /**
     * Transaction registered webhook type (TransactionSystemEvents::TRANSACTION_REGISTERED).
     */
    const TRANSACTION_REGISTERED_WEBHOOK_TYPE = 'transaction.registered';

    /**
     * Transaction assigned to customer webhook type (TransactionSystemEvents::CUSTOMER_ASSIGNED_TO_TRANSACTION).
     */
    const CUSTOMER_ASSIGNED_TO_TRANSACTION_WEBHOOK_TYPE = 'transaction.assigned_to_customer';

    /**
     * @param TransactionRegisteredEvent $event
     */
    public function onTransactionRegistered(TransactionRegisteredEvent $event): void
    {
        $this->uniqueDispatchCommand(
            self::TRANSACTION_REGISTERED_WEBHOOK_TYPE,
            [
                'transactionId' => (string) $event->getTransactionId(),
                'transactionData' => $event->getTransactionData(),
                'customerData' => $event->getCustomerData(),
                'items' => $event->getItems(),
                'postId' => $event->getPosId() ? (string) $event->getPosId() : null,
            ]
        );
    }

    /**
     * @param CustomerAssignedToTransactionSystemEvent $event
     */
    public function onTransactionAssignedToCustomer(CustomerAssignedToTransactionSystemEvent $event): void
    {
        $this->uniqueDispatchCommand(
            self::CUSTOMER_ASSIGNED_TO_TRANSACTION_WEBHOOK_TYPE,
            [
                'transactionId' => (string) $event->getTransactionId()->__toString(),
                'customerId' => $event->getCustomerId()->__toString(),
            ]
        );
    }
}
