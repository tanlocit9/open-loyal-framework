<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Core\Infrastructure\Repository;

use Broadway\Domain\AggregateRoot;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStore;
use Broadway\Repository\Repository;
use Broadway\Snapshotting\Snapshot\Snapshot;
use Broadway\Snapshotting\Snapshot\SnapshotRepository;
use Broadway\Snapshotting\Snapshot\Trigger;
use OpenLoyalty\Component\Core\Domain\SnapableEventSourcedAggregateRoot;

/**
 * Class SnapshottingEventSourcingRepository.
 */
class SnapshottingEventSourcingRepository implements Repository
{
    private $eventSourcingRepository;
    private $eventStore;
    private $snapshotRepository;
    private $trigger;

    /**
     * SnapshottingEventSourcingRepository constructor.
     *
     * @param EventSourcingRepository $eventSourcingRepository
     * @param EventStore              $eventStore
     * @param SnapshotRepository      $snapshotRepository
     * @param Trigger                 $trigger
     */
    public function __construct(
        EventSourcingRepository $eventSourcingRepository,
        EventStore $eventStore,
        SnapshotRepository $snapshotRepository,
        Trigger $trigger
    ) {
        $this->eventSourcingRepository = $eventSourcingRepository;
        $this->eventStore = $eventStore;
        $this->snapshotRepository = $snapshotRepository;
        $this->trigger = $trigger;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id): AggregateRoot
    {
        $snapshot = $this->snapshotRepository->load($id);
        if (null === $snapshot) {
            return $this->eventSourcingRepository->load($id);
        }

        $aggregateRoot = $snapshot->getAggregateRoot();

        if (!$aggregateRoot instanceof SnapableEventSourcedAggregateRoot) {
            return $this->eventSourcingRepository->load($id);
        }

        $aggregateRoot->initializeState(
            $this->eventStore->loadFromPlayhead($id, $snapshot->getPlayhead() + 1),
            $snapshot
        );

        return $aggregateRoot;
    }

    /**
     * {@inheritdoc}
     */
    public function save(AggregateRoot $aggregate)
    {
        $takeSnapshot = false;

        if ($aggregate instanceof EventSourcedAggregateRoot) {
            $takeSnapshot = $this->trigger->shouldSnapshot($aggregate);
        }

        $this->eventSourcingRepository->save($aggregate);

        if ($takeSnapshot) {
            $this->snapshotRepository->save(new Snapshot($aggregate));
        }
    }
}
