<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\EventDispatcher\EventDispatcher;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion;
use OpenLoyalty\Component\Segment\Domain\Model\SegmentPart;
use OpenLoyalty\Component\Segment\Domain\Segment;
use OpenLoyalty\Component\Segment\Domain\SegmentPartId;
use OpenLoyalty\Component\Segment\Domain\SegmentPartRepository;
use OpenLoyalty\Component\Segment\Domain\SegmentRepository;
use OpenLoyalty\Component\Segment\Domain\SystemEvent\SegmentChangedSystemEvent;
use OpenLoyalty\Component\Segment\Domain\SystemEvent\SegmentSystemEvents;

/**
 * Class SegmentCommandHandler.
 */
class SegmentCommandHandler extends SimpleCommandHandler
{
    /**
     * @var SegmentRepository
     */
    protected $segmentRepository;

    /**
     * @var SegmentPartRepository
     */
    protected $segmentPartRepository;
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * SegmentCommandHandler constructor.
     *
     * @param SegmentRepository     $segmentRepository
     * @param SegmentPartRepository $segmentPartRepository
     * @param EventDispatcher       $eventDispatcher
     */
    public function __construct(
        SegmentRepository $segmentRepository,
        SegmentPartRepository $segmentPartRepository,
        EventDispatcher $eventDispatcher
    ) {
        $this->segmentRepository = $segmentRepository;
        $this->segmentPartRepository = $segmentPartRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleCreateSegment(CreateSegment $command)
    {
        $data = $command->getSegmentData();
        $segment = new Segment($command->getSegmentId(), $data['name'], $data['description']);

        $partsData = $data['parts'];
        foreach ($partsData as $part) {
            $newPart = new SegmentPart(new SegmentPartId($part['segmentPartId']));
            $criteriaData = $part['criteria'];
            foreach ($criteriaData as $criterion) {
                $class = Criterion::TYPE_MAP[$criterion['type']];

                $criterion = $class::fromArray($criterion);
                $newPart->addCriterion($criterion);
            }
            $segment->addPart($newPart);
        }

        $this->segmentRepository->save($segment);
    }

    public function handleUpdateSegment(UpdateSegment $command)
    {
        $data = $command->getSegmentData();
        /** @var Segment $segment */
        $segment = $this->segmentRepository->byId($command->getSegmentId());
        if (isset($data['name'])) {
            $segment->setName($data['name']);
        }
        if (isset($data['description'])) {
            $segment->setDescription($data['description']);
        }
        if (isset($data['parts'])) {
            foreach ($segment->getParts() as $part) {
                $segment->removePart($part);
                $this->segmentPartRepository->remove($part);
            }
            $partsData = $data['parts'];
            foreach ($partsData as $part) {
                $newPart = new SegmentPart(new SegmentPartId($part['segmentPartId']));
                $criteriaData = $part['criteria'];
                foreach ($criteriaData as $criterion) {
                    $class = Criterion::TYPE_MAP[$criterion['type']];

                    $criterion = $class::fromArray($criterion);
                    $newPart->addCriterion($criterion);
                }
                $segment->addPart($newPart);
            }
        }

        $this->segmentRepository->save($segment);
        if ($this->eventDispatcher && $segment->isActive() === true) {
            $this->eventDispatcher->dispatch(
                SegmentSystemEvents::SEGMENT_CHANGED,
                [
                    new SegmentChangedSystemEvent($segment->getSegmentId(), $segment->getName()),
                ]
            );
        }
    }

    public function handleActivateSegment(ActivateSegment $command)
    {
        /** @var Segment $segment */
        $segment = $this->segmentRepository->byId($command->getSegmentId());
        $segment->setActive(true);
        $this->segmentRepository->save($segment);
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(
                SegmentSystemEvents::SEGMENT_CHANGED,
                [
                    new SegmentChangedSystemEvent($segment->getSegmentId(), $segment->getName()),
                ]
            );
        }
    }

    public function handleDeactivateSegment(DeactivateSegment $command)
    {
        /** @var Segment $segment */
        $segment = $this->segmentRepository->byId($command->getSegmentId());
        $segment->setActive(false);
        $this->segmentRepository->save($segment);
    }

    public function handleDeleteSegment(DeleteSegment $command)
    {
        /** @var Segment $segment */
        $segment = $this->segmentRepository->byId($command->getSegmentId());
        $this->segmentRepository->remove($segment);
    }
}
