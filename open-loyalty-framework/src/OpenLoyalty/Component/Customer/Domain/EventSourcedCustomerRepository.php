<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain;

use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventHandling\EventBus;
use Broadway\EventStore\EventStore;

/**
 * Class EventSourcedCustomerRepository.
 */
class EventSourcedCustomerRepository extends EventSourcingRepository
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        EventStore $eventStore,
        EventBus $eventBus,
        array $eventStreamDecorators = array()
    ) {
        parent::__construct(
            $eventStore,
            $eventBus,
            '\OpenLoyalty\Component\Customer\Domain\Customer',
            new PublicConstructorAggregateFactory(),
            $eventStreamDecorators
        );
    }
}
