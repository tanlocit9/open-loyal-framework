<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\LevelBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\LevelBundle\Security\Voter\LevelVoter;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class LevelVoterTest.
 */
class LevelVoterTest extends BaseVoterTest
{
    const LEVEL_ID = '00000000-0000-474c-b092-b0dd880c0700';
    const LEVEL2_ID = '00000000-0000-474c-b092-b0dd880c0702';

    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            LevelVoter::CREATE_LEVEL => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
            LevelVoter::EDIT => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => self::LEVEL_ID],
            LevelVoter::VIEW => ['seller' => true, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => self::LEVEL2_ID],
            LevelVoter::ACTIVATE => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => self::LEVEL2_ID],
            LevelVoter::LIST_LEVELS => ['seller' => true, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
            LevelVoter::LIST_CUSTOMERS => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => self::LEVEL2_ID],
            LevelVoter::CUSTOMER_LIST_LEVELS => ['seller' => false, 'customer' => true, 'admin' => false, 'admin_reporter' => false, 'id' => self::LEVEL_ID],
        ];

        $voter = new LevelVoter();

        $this->assertVoterAttributes($voter, $attributes);
    }

    /**
     * @param $id
     *
     * @return MockObject
     */
    protected function getSubjectById($id)
    {
        $level = $this->getMockBuilder(Level::class)->disableOriginalConstructor()->getMock();
        $level->method('getLevelId')->willReturn(new LevelId($id));

        return $level;
    }
}
