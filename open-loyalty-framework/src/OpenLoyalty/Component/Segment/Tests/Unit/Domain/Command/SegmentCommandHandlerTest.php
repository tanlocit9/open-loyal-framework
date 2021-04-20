<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Segment\Tests\Unit\Domain\Command;

use Broadway\EventDispatcher\EventDispatcher;
use OpenLoyalty\Component\Segment\Domain\Command\SegmentCommandHandler;
use OpenLoyalty\Component\Segment\Domain\Segment;
use OpenLoyalty\Component\Segment\Domain\SegmentId;
use OpenLoyalty\Component\Segment\Domain\SegmentRepository;
use OpenLoyalty\Component\Segment\Domain\SegmentPartRepository;
use PHPUnit\Framework\TestCase;

/**
 * Class SegmentCommandHandlerTest.
 */
abstract class SegmentCommandHandlerTest extends TestCase
{
    /**
     * @var SegmentRepository
     */
    protected $inMemoryRepository;

    /**
     * @var SegmentPartRepository
     */
    protected $partsInMemoryRepository;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var array
     */
    protected $parts = [];

    /**
     * @var array
     */
    protected $segment = [];

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $segment = new Segment(new SegmentId('00000000-0000-0000-0000-000000001111'), 'test');
        $this->segment[] = $segment;

        $segments = &$this->segment;
        $this->inMemoryRepository = $this->getMockBuilder(SegmentRepository::class)->getMock();
        $this->partsInMemoryRepository = $this->getMockBuilder(SegmentPartRepository::class)->getMock();
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcher::class)->getMock();
        $this->partsInMemoryRepository->method('remove')->with($this->any())->willReturn(true);
        $this->inMemoryRepository->method('save')->with($this->isInstanceOf(Segment::class))->will(
            $this->returnCallback(function ($segment) use (&$segments) {
                $segments[] = $segment;

                return $segment;
            })
        );
        $this->inMemoryRepository->method('findAll')->with()->will(
            $this->returnCallback(function () use (&$segments) {
                return $segments;
            })
        );
        $this->inMemoryRepository->method('byId')->with($this->isInstanceOf(SegmentId::class))->will(
            $this->returnCallback(function ($id) use (&$segments) {
                /** @var Segment $segment */
                foreach ($segments as $segment) {
                    if ((string) $segment->getSegmentId() == (string) $id) {
                        return $segment;
                    }
                }

                return;
            })
        );
    }

    /**
     * @return SegmentCommandHandler
     */
    protected function createCommandHandler(): SegmentCommandHandler
    {
        return new SegmentCommandHandler(
            $this->inMemoryRepository,
            $this->partsInMemoryRepository,
            $this->eventDispatcher
        );
    }
}
