<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Transaction\Tests\Unit\Command;

use Broadway\CommandHandling\CommandHandler;
use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\EventHandling\EventBus;
use Broadway\EventStore\EventStore;
use Broadway\Snapshotting\Snapshot\SnapshotRepository;
use Broadway\Snapshotting\Snapshot\Trigger;
use OpenLoyalty\Component\Transaction\Domain\Command\TransactionCommandHandler;
use OpenLoyalty\Component\Transaction\Domain\EventSourcedTransactionRepository;
use OpenLoyalty\Component\Transaction\Domain\TransactionRepository;

/**
 * Class TransactionCommandHandlerTest.
 */
abstract class TransactionCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createCommandHandler(EventStore $eventStore, EventBus $eventBus): CommandHandler
    {
        /** @var EventDispatcher|\PHPUnit_Framework_MockObject_MockObject $eventDispatcher */
        $eventDispatcher = $this->getMockBuilder(EventDispatcher::class)->getMock();
        $eventDispatcher->method('dispatch')->with($this->isType('string'))->willReturn(true);

        $eventSourcedRepository = new EventSourcedTransactionRepository($eventStore, $eventBus);

        /** @var Trigger|\PHPUnit_Framework_MockObject_MockObject $trigger */
        $trigger = $this->getMockBuilder(Trigger::class)->getMock();

        /** @var SnapshotRepository|\PHPUnit_Framework_MockObject_MockObject $snapshotRepository */
        $snapshotRepository = $this->getMockBuilder(SnapshotRepository::class)->getMock();

        return new TransactionCommandHandler(
            new TransactionRepository($eventSourcedRepository, $eventStore, $snapshotRepository, $trigger),
            $eventDispatcher
        );
    }
}
