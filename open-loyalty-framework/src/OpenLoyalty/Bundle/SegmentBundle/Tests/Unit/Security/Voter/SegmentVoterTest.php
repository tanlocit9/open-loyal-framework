<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\SegmentBundle\Security\Voter\SegmentVoter;
use OpenLoyalty\Component\Campaign\Domain\SegmentId;
use OpenLoyalty\Component\Segment\Domain\Segment;

/**
 * Class SegmentVoterTest.
 */
class SegmentVoterTest extends BaseVoterTest
{
    const SEGMENT_ID = '00000000-0000-474c-b092-b0dd880c0700';

    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            SegmentVoter::LIST_SEGMENTS => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
            SegmentVoter::CREATE_SEGMENT => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
            SegmentVoter::LIST_CUSTOMERS => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => self::SEGMENT_ID],
            SegmentVoter::EDIT => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => self::SEGMENT_ID],
            SegmentVoter::VIEW => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => self::SEGMENT_ID],
            SegmentVoter::ACTIVATE => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => self::SEGMENT_ID],
            SegmentVoter::DELETE => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => self::SEGMENT_ID],
            SegmentVoter::DEACTIVATE => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => self::SEGMENT_ID],
        ];

        $voter = new SegmentVoter();

        $this->assertVoterAttributes($voter, $attributes);
    }

    protected function getSubjectById($id)
    {
        $segment = $this->getMockBuilder(Segment::class)->disableOriginalConstructor()->getMock();
        $segment->method('getSegmentId')->willReturn(new SegmentId($id));

        return $segment;
    }
}
