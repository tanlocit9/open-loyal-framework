<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\Command;

use Broadway\EventDispatcher\EventDispatcher;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use OpenLoyalty\Component\Customer\Domain\Command\RemoveManuallyAssignedLevel;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerSystemEvents;

/**
 * Class RemoveManuallyAssignedLevelTest.
 */
class RemoveManuallyAssignedLevelTest extends CustomerCommandHandlerTest
{
    /**
     * @test
     */
    public function it_dispatch_event_on_remove_level_assigned_manually()
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');
        $eventStore = new TraceableEventStore(new InMemoryEventStore());

        $eventBus = new SimpleEventBus();
        $eventDispatcher = $this->getMockBuilder(EventDispatcher::class)->getMock();
        $eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with($this->logicalOr(
                $this->equalTo(CustomerSystemEvents::CUSTOMER_UPDATED),
                $this->equalTo(CustomerSystemEvents::CUSTOMER_MANUALLY_LEVEL_REMOVED)
            ))
            ->willReturn(true);

        $handler = $this->getCustomerCommandHandler($eventStore, $eventBus, $eventDispatcher);
        $handler->handle(new RemoveManuallyAssignedLevel($customerId));
    }
}
