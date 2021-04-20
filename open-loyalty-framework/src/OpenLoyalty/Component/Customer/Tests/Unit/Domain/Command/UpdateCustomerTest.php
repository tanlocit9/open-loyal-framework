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
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerDetails;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerDetailsWereUpdated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasRegistered;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerAgreementsUpdatedSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerSystemEvents;

/**
 * Class UpdateCustomerTest.
 */
final class UpdateCustomerTest extends CustomerCommandHandlerTest
{
    /**
     * @test
     */
    public function it_updates_customer_name()
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');
        $this->scenario
            ->withAggregateId((string) $customerId)
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
            ])
            ->when(new UpdateCustomerDetails($customerId, ['firstName' => 'Jane']))
            ->then([
                new CustomerDetailsWereUpdated($customerId, ['firstName' => 'Jane']),
            ]);
    }

    /**
     * @test
     */
    public function it_dispatch_event_on_update()
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

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
        $handler->handle(new UpdateCustomerDetails($customerId, ['firstName' => 'Jane']));
    }

    /**
     * @test
     */
    public function it_dispatch_event_on_update_and_separate_event_for_agreements()
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $eventStore = new TraceableEventStore(new InMemoryEventStore());

        $messages[] = DomainMessage::recordNow($customerId, 0, new Metadata(array()), new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()));

        $eventStore->append($customerId, new DomainEventStream($messages));

        $eventBus = new SimpleEventBus();
        $eventDispatcher = $this->getMockBuilder(EventDispatcher::class)->getMock();
        $eventDispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(
                $this->equalTo(CustomerSystemEvents::CUSTOMER_AGREEMENTS_UPDATED),
                $this->equalTo(
                    [new CustomerAgreementsUpdatedSystemEvent(
                        $customerId,
                        [
                            'agreement2' => [
                                'old' => false,
                                'new' => true,
                            ],
                        ]
                    )]
                )
            )
            ->willReturn(true);

        $eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                $this->equalTo(CustomerSystemEvents::CUSTOMER_UPDATED),
                $this->isType('array')
            )
            ->willReturn(true);

        $handler = $this->getCustomerCommandHandler($eventStore, $eventBus, $eventDispatcher);
        $handler->handle(new UpdateCustomerDetails($customerId, ['firstName' => 'Jane', 'agreement2' => true]));
    }
}
