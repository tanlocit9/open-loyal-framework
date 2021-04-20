<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\Command;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use OpenLoyalty\Component\Customer\Domain\Command\AssignSellerToCustomer;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasRegistered;
use OpenLoyalty\Component\Customer\Domain\Event\SellerWasAssignedToCustomer;
use OpenLoyalty\Component\Customer\Domain\SellerId;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerSystemEvents;

/**
 * Class AssignSellerToCustomerTest.
 */
final class AssignSellerToCustomerTest extends CustomerCommandHandlerTest
{
    /**
     * @test
     */
    public function it_updates_customer_name()
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');
        $sellerId = new SellerId('00000000-0000-0000-0000-000000000011');
        $this->scenario
            ->withAggregateId((string) $customerId)
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
            ])
            ->when(new AssignSellerToCustomer($customerId, $sellerId))
            ->then([
                new SellerWasAssignedToCustomer($customerId, $sellerId),
            ]);
    }

    /**
     * @test
     */
    public function it_dispatch_event_on_update()
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');
        $sellerId = new SellerId('00000000-0000-0000-0000-000000000011');

        $eventStore = new TraceableEventStore(new InMemoryEventStore());

        $messages[] = DomainMessage::recordNow($customerId, 0, new Metadata(array()), new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()));

        $eventStore->append($customerId, new DomainEventStream($messages));

        $eventBus = new SimpleEventBus();
        $eventDispatcher = $this->getMockBuilder(EventDispatcher::class)->getMock();
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo(CustomerSystemEvents::CUSTOMER_UPDATED))
            ->willReturn(true);
        $handler = $this->getCustomerCommandHandler($eventStore, $eventBus, $eventDispatcher);
        $handler->handle(new AssignSellerToCustomer($customerId, $sellerId));
    }
}
