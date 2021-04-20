<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Core\Domain;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\Snapshotting\Snapshot\Snapshot;

/**
 * Class SnapableEventSourcedAggregateRoot.
 *
 * This class overrides EventSourcedAggregateRoot as this class is closed for extension
 * and playhead cannot be accessed from child classes.
 */
abstract class SnapableEventSourcedAggregateRoot extends EventSourcedAggregateRoot
{
    /**
     * @var array
     */
    private $uncommittedEvents = [];
    private $playhead = -1; // 0-based playhead allows events[0] to contain playhead 0

    /**
     * {@inheritdoc}
     */
    public function apply($event)
    {
        $this->handleRecursively($event);

        ++$this->playhead;
        $this->uncommittedEvents[] = DomainMessage::recordNow(
            $this->getAggregateRootId(),
            $this->playhead,
            new Metadata([]),
            $event
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUncommittedEvents(): DomainEventStream
    {
        $stream = new DomainEventStream($this->uncommittedEvents);

        $this->uncommittedEvents = [];

        return $stream;
    }

    /**
     * {@inheritdoc}
     *
     * @param Snapshot|null $snapshot
     */
    public function initializeState(DomainEventStream $stream, Snapshot $snapshot = null)
    {
        if ($snapshot) {
            $this->playhead = $snapshot->getPlayhead();
        }

        foreach ($stream as $message) {
            ++$this->playhead;
            $this->handleRecursively($message->getPayload());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function handle($event)
    {
        $method = $this->getApplyMethod($event);

        if (!method_exists($this, $method)) {
            return;
        }

        $this->$method($event);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleRecursively($event)
    {
        $this->handle($event);

        foreach ($this->getChildEntities() as $entity) {
            $entity->registerAggregateRoot($this);
            $entity->handleRecursively($event);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getChildEntities(): array
    {
        return [];
    }

    /**
     * @param $event
     *
     * @return string
     */
    private function getApplyMethod($event): string
    {
        $classParts = explode('\\', get_class($event));

        return 'apply'.end($classParts);
    }

    /**
     * {@inheritdoc}
     */
    public function getPlayhead(): int
    {
        return $this->playhead;
    }
}
