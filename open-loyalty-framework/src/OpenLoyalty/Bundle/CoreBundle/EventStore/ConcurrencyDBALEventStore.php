<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CoreBundle\EventStore;

use Broadway\EventStore\ConcurrencyConflictResolver\ConcurrencyConflictResolver;
use Broadway\EventStore\Dbal\DBALEventStore;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\EventVisitor;
use Broadway\EventStore\Exception\DuplicatePlayheadException;
use Broadway\EventStore\Management\Criteria;
use Broadway\EventStore\Management\EventStoreManagement;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

/**
 * Class ConcurrencyDBALEventStore.
 */
class ConcurrencyDBALEventStore implements EventStore, EventStoreManagement
{
    /**
     * @var DBALEventStore
     */
    protected $eventStore;

    /**
     * @var ConcurrencyConflictResolver
     */
    protected $conflictResolver;

    /**
     * ConcurrencyDBALEventStore constructor.
     *
     * @param DBALEventStore              $eventStore
     * @param ConcurrencyConflictResolver $conflictResolver
     */
    public function __construct(DBALEventStore $eventStore, ConcurrencyConflictResolver $conflictResolver)
    {
        $this->eventStore = $eventStore;
        $this->conflictResolver = $conflictResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function append($id, DomainEventStream $uncommittedEvents)
    {
        try {
            $this->eventStore->append($id, $uncommittedEvents);
        } catch (DuplicatePlayheadException $e) {
            $committedEvents = $this->eventStore->load($id);
            $conflictingEvents = $this->getConflictingEvents($uncommittedEvents, $committedEvents);

            $conflictResolvedEvents = [];
            $playhead = $this->getCurrentPlayhead($committedEvents);

            /** @var DomainMessage $uncommittedEvent */
            foreach ($uncommittedEvents as $uncommittedEvent) {
                foreach ($conflictingEvents as $conflictingEvent) {
                    if ($this->conflictResolver->conflictsWith($conflictingEvent, $uncommittedEvent)) {
                        throw $e;
                    }
                }

                ++$playhead;

                $conflictResolvedEvents[] = new DomainMessage(
                    $id,
                    $playhead,
                    $uncommittedEvent->getMetadata(),
                    $uncommittedEvent->getPayload(),
                    $uncommittedEvent->getRecordedOn()
                );
            }

            $this->append($id, new DomainEventStream($conflictResolvedEvents));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load($id): DomainEventStream
    {
        return $this->eventStore->load($id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromPlayhead($id, int $playhead): DomainEventStream
    {
        return $this->eventStore->loadFromPlayhead($id, $playhead);
    }

    /**
     * {@inheritdoc}
     */
    public function visitEvents(Criteria $criteria, EventVisitor $eventVisitor)
    {
        return $this->visitEvents($criteria, $eventVisitor);
    }

    /**
     * @param Schema $schema
     *
     * @return Table|null
     */
    public function configureSchema(Schema $schema): ?Table
    {
        return $this->eventStore->configureSchema($schema);
    }

    /**
     * @return Table
     */
    public function configureTable(): Table
    {
        return $this->eventStore->configureTable();
    }

    /**
     * @param DomainEventStream $committedEvents
     *
     * @return int
     */
    private function getCurrentPlayhead(DomainEventStream $committedEvents): int
    {
        $events = iterator_to_array($committedEvents);
        /** @var DomainMessage $lastEvent */
        $lastEvent = end($events);
        $playhead = $lastEvent->getPlayhead();

        return $playhead;
    }

    /**
     * @param DomainEventStream $uncommittedEvents
     * @param DomainEventStream $committedEvents
     *
     * @return array
     */
    private function getConflictingEvents(
        DomainEventStream $uncommittedEvents,
        DomainEventStream $committedEvents
    ): array {
        $conflictingEvents = [];

        /** @var DomainMessage $committedEvent */
        foreach ($committedEvents as $committedEvent) {
            /** @var DomainMessage $uncommittedEvent */
            foreach ($uncommittedEvents as $uncommittedEvent) {
                if ($committedEvent->getPlayhead() >= $uncommittedEvent->getPlayhead()) {
                    $conflictingEvents[] = $committedEvent;

                    break;
                }
            }
        }

        return $conflictingEvents;
    }
}
