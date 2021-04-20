<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\CoreBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\SegmentBundle\Security\Voter\SegmentVoter as BaseSegmentVoter;
use OpenLoyalty\Bundle\CoreBundle\Security\Voter\SegmentVoter;
use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\UtilityBundle\Security\Voter\UtilityVoter;
use OpenLoyalty\Component\Segment\Domain\Segment;
use OpenLoyalty\Component\Segment\Domain\SegmentId;
use OpenLoyalty\Bundle\SegmentBundle\Tests\Unit\Security\Voter\SegmentVoterTest as BaseSegmentVoterTest;

/**
 * Class SegmentVoterTest.
 */
final class SegmentVoterTest extends BaseVoterTest
{
    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            BaseSegmentVoter::LIST_CUSTOMERS => [
                'seller' => false,
                'customer' => false,
                'admin' => true,
                'admin_reporter' => true,
                'id' => BaseSegmentVoterTest::SEGMENT_ID,
            ],
            UtilityVoter::GENERATE_CSV_BY_SEGMENT => [
                'seller' => false,
                'customer' => false,
                'admin' => true,
                'admin_reporter' => true,
            ],
        ];

        $voter = new SegmentVoter();

        $this->assertVoterAttributes($voter, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubjectById($id)
    {
        $segment = $this->getMockBuilder(Segment::class)->disableOriginalConstructor()->getMock();
        $segment->method('getSegmentId')->willReturn(new SegmentId($id));

        return $segment;
    }
}
