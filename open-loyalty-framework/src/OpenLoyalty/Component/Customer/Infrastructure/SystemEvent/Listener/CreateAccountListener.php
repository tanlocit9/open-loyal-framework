<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Infrastructure\SystemEvent\Listener;

use Broadway\CommandHandling\CommandBus;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Command\CreateAccount;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerRegisteredSystemEvent;
use OpenLoyalty\Component\Account\Domain\CustomerId as AccountCustomerId;

/**
 * Class CreateAccountListener.
 */
class CreateAccountListener
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * CreateAccountListener constructor.
     *
     * @param CommandBus             $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(CommandBus $commandBus, UuidGeneratorInterface $uuidGenerator)
    {
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
    }

    public function handleCustomerRegistered(CustomerRegisteredSystemEvent $event)
    {
        $this->commandBus->dispatch(new CreateAccount(
            new AccountId($this->uuidGenerator->generate()),
            new AccountCustomerId($event->getCustomerId()->__toString())
        ));
    }
}
