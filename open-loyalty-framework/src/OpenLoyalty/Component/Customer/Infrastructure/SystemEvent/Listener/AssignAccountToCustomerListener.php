<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Infrastructure\SystemEvent\Listener;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Component\Account\Domain\SystemEvent\AccountCreatedSystemEvent;
use OpenLoyalty\Component\Customer\Domain\AccountId;
use OpenLoyalty\Component\Customer\Domain\Command\AssignAccountToCustomer;
use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class AssignAccountToCustomerListener.
 */
class AssignAccountToCustomerListener
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * AssignAccountToCustomerListener constructor.
     *
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param AccountCreatedSystemEvent $event
     */
    public function handle(AccountCreatedSystemEvent $event): void
    {
        $this->commandBus->dispatch(
            new AssignAccountToCustomer(
                new CustomerId((string) $event->getCustomerId()),
                new AccountId((string) $event->getAccountId())
            )
        );
    }
}
