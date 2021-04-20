<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain;

use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStore;
use Broadway\Snapshotting\Snapshot\SnapshotRepository;
use Broadway\Snapshotting\Snapshot\Trigger;
use OpenLoyalty\Component\Core\Infrastructure\Repository\SnapshottingEventSourcingRepository;

/**
 * Class AccountRepository.
 */
class AccountRepository extends SnapshottingEventSourcingRepository
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
