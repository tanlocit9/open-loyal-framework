<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PosBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\PosBundle\Security\Voter\PosVoter;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosId;

/**
 * Class PosVoterTest.
 */
class PosVoterTest extends BaseVoterTest
{
    const POS_ID = '00000000-0000-474c-b092-b0dd880c0700';

    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            PosVoter::LIST_POS => ['seller' => true, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
            PosVoter::CREATE_POS => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
            PosVoter::EDIT => ['seller' => false, 'customer' => false, 'admin' => true, 'id' => self::POS_ID, 'admin_reporter' => false],
            PosVoter::VIEW => ['seller' => true, 'customer' => false, 'admin' => true, 'id' => self::POS_ID, 'admin_reporter' => true],
        ];

        $voter = new PosVoter();

        $this->assertVoterAttributes($voter, $attributes);
    }

    protected function getSubjectById($id)
    {
        $level = $this->getMockBuilder(Pos::class)->disableOriginalConstructor()->getMock();
        $level->method('getPosId')->willReturn(new PosId($id));

        return $level;
    }
}
