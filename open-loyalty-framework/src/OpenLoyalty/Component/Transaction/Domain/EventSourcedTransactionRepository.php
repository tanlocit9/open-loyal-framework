<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Transaction\Domain;

use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventHandling\EventBus;
use Broadway\EventStore\EventStore;

/**
 * Class EventSourcedTransactionRepository.
 */
class EventSourcedTransactionRepository extends EventSourcingRepository
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
            '\OpenLoyalty\Component\Transaction\Domain\Transaction',
            new PublicConstructorAggregateFactory(),
            $eventStreamDecorators
        );
    }
}
