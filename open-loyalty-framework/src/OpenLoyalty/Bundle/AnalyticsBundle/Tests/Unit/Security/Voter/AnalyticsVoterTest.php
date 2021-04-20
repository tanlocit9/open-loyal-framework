<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\AnalyticsBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\AnalyticsBundle\Security\Voter\AnalyticsVoter;
use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;

/**
 * Class AnalyticsVoterTest.
 */
class AnalyticsVoterTest extends BaseVoterTest
{
    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            AnalyticsVoter::VIEW_STATS => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
        ];

        $voter = new AnalyticsVoter();

        $this->assertVoterAttributes($voter, $attributes);
    }

    /**
     * @param $id
     */
    protected function getSubjectById($id)
    {
        return;
    }
}
