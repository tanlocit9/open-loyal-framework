<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Seller\Tests\Unit\Domain\Command;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBus;
use Broadway\EventStore\EventStore;
use Broadway\CommandHandling\CommandHandler;
use OpenLoyalty\Component\Seller\Domain\Command\SellerCommandHandler;
use OpenLoyalty\Component\Seller\Domain\SellerRepository;
use OpenLoyalty\Component\Seller\Domain\Validator\SellerUniqueValidator;

/**
 * Class SellerCommandHandlerTest.
 */
abstract class SellerCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    protected function createCommandHandler(EventStore $eventStore, EventBus $eventBus): CommandHandler
    {
        $sellerDetailsRepository = $this->getMockBuilder('Broadway\ReadModel\Repository')->getMock();
        $sellerDetailsRepository->method('findBy')->willReturn([]);
        $validator = new SellerUniqueValidator($sellerDetailsRepository);

        return new SellerCommandHandler(new SellerRepository($eventStore, $eventBus), $validator);
    }
}
