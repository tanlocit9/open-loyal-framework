<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Account\Tests\Unit\Domain\Command;

use Broadway\CommandHandling\CommandHandler;
use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBus;
use Broadway\EventStore\EventStore;
use Broadway\Snapshotting\Snapshot\SnapshotRepository;
use Broadway\Snapshotting\Snapshot\Trigger;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Component\Account\Domain\AccountRepository;
use OpenLoyalty\Component\Account\Domain\Command\AccountCommandHandler;
use PHPUnit\Framework\MockObject\MockObject;
use OpenLoyalty\Component\Account\Domain\EventSourcedAccountRepository;

/**
 * Class AccountCommandHandlerTest.
 */
abstract class AccountCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    public static $uuidCount = 0;

    /**
     * Create a command handler for the given scenario test case.
     *
     * @param EventStore $eventStore
     * @param EventBus   $eventBus
     *
     * @return CommandHandler
     */
    protected function createCommandHandler(EventStore $eventStore, EventBus $eventBus): CommandHandler
    {
        $eventSourcedAccountRepository = new EventSourcedAccountRepository($eventStore, $eventBus);

        /** @var Trigger|\PHPUnit_Framework_MockObject_MockObject $trigger */
        $trigger = $this->getMockBuilder(Trigger::class)->getMock();

        /** @var SnapshotRepository|\PHPUnit_Framework_MockObject_MockObject $snapshotRepository */
        $snapshotRepository = $this->getMockBuilder(SnapshotRepository::class)->getMock();

        return new AccountCommandHandler(
            new AccountRepository($eventSourcedAccountRepository, $eventStore, $snapshotRepository, $trigger),
            $this->getUuidGenerator()
        );
    }

    /**
     * @return MockObject|UuidGeneratorInterface
     */
    protected function getUuidGenerator()
    {
        $mock = $this->getMockBuilder(UuidGeneratorInterface::class)->getMock();
        $mock->method('generate')->willReturnCallback(function () {
            $uuid = sprintf('00000000-0000-0000-0000-000000000%03d', self::$uuidCount);
            ++self::$uuidCount;

            return $uuid;
        });

        return $mock;
    }
}
