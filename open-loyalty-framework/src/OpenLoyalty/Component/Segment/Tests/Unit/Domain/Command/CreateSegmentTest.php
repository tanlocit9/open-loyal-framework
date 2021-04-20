<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Segment\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Segment\Domain\Command\CreateSegment;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion;
use OpenLoyalty\Component\Segment\Domain\Segment;
use OpenLoyalty\Component\Segment\Domain\SegmentId;

/**
 * Class CreateSegmentTest.
 */
class CreateSegmentTest extends SegmentCommandHandlerTest
{
    /**
     * @test
     */
    public function it_creates_new_segment()
    {
        $handler = $this->createCommandHandler();
        $segmentId = new SegmentId('00000000-0000-0000-0000-000000000000');
        $posId = '00000000-0000-0000-0000-000000000000';

        $command = new CreateSegment($segmentId, [
            'name' => 'test',
            'description' => 'desc',
            'parts' => [
                [
                    'segmentPartId' => '00000000-0000-0000-0000-000000000000',
                    'criteria' => [
                        [
                            'type' => Criterion::TYPE_BOUGHT_IN_POS,
                            'criterionId' => '00000000-0000-0000-0000-000000000000',
                            'posIds' => [$posId],
                        ],
                        [
                            'type' => Criterion::TYPE_AVERAGE_TRANSACTION_AMOUNT,
                            'criterionId' => '00000000-0000-0000-0000-000000000001',
                            'fromAmount' => 1,
                            'toAmount' => 10000,
                        ],
                        [
                            'type' => Criterion::TYPE_TRANSACTION_COUNT,
                            'criterionId' => '00000000-0000-0000-0000-000000000002',
                            'min' => 10,
                            'max' => 20,
                        ],
                    ],
                ],
            ],
        ]);
        $handler->handle($command);
        /** @var Segment $segment */
        $segment = $this->inMemoryRepository->byId($segmentId);
        $this->assertNotNull($segment);
        $this->assertInstanceOf(Segment::class, $segment);
        $this->assertTrue(count($segment->getParts()) == 1);
        $parts = $segment->getParts();
        $part = reset($parts);
        $this->assertTrue(count($part->getCriteria()) == 3);
    }
}
