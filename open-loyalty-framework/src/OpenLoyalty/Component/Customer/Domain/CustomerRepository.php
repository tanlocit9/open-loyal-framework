<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain;

use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStore;
use Broadway\Snapshotting\Snapshot\SnapshotRepository;
use Broadway\Snapshotting\Snapshot\Trigger;
use OpenLoyalty\Component\Core\Infrastructure\Repository\SnapshottingEventSourcingRepository;

/**
 * Class CustomerRepository.
 */
class CustomerRepository extends SnapshottingEventSourcingRepository
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        EventSourcingRepository $eventSourcingRepository,
        EventStore $eventStore,
        SnapshotRepository $snapshotRepository,
        Trigger $trigger
    ) {
        parent::__construct($eventSourcingRepository, $eventStore, $snapshotRepository, $trigger);
    }
}
