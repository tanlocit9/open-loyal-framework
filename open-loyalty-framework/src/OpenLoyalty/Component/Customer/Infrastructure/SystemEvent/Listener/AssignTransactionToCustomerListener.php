<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Infrastructure\SystemEvent\Listener;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Component\Customer\Domain\Command\AssignTransactionToCustomer;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\CustomerAssignedToTransactionSystemEvent;

/**
 * Class AssignTransactionToCustomerListener.
 */
class AssignTransactionToCustomerListener
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * AssignTransactionToCustomerListener constructor.
     *
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param CustomerAssignedToTransactionSystemEvent $event
     */
    public function handle(CustomerAssignedToTransactionSystemEvent $event)
    {
        $this->commandBus->dispatch(
            new AssignTransactionToCustomer(
                (string) $event->getCustomerId(),
                (string) $event->getTransactionId(),
                $event->getGrossValue(),
                $event->getGrossValueWithoutDeliveryCosts(),
                $event->getDocumentNumber(),
                $event->getAmountExcludedForLevel(),
                $event->isReturn(),
                $event->getRevisedDocument()
            )
        );
    }
}
