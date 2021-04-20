<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Bundle\CoreBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Security\Voter\LevelVoter;
use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\LevelBundle\Security\Voter\LevelVoter as BaseLevelVoter;
use OpenLoyalty\Bundle\LevelBundle\Tests\Unit\Security\Voter\LevelVoterTest as BaseLevelVoterTest;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\CustomerVoter;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;

/**
 * Class LevelVoterTest.
 */
final class LevelVoterTest extends BaseVoterTest
{
    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            BaseLevelVoter::LIST_CUSTOMERS => [
                'seller' => false,
                'customer' => false,
                'admin' => true,
                'admin_reporter' => true,
                'admin_custom' => [
                    [
                        'permissions' => [
                            BaseLevelVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW],
                        ],
                        'expected' => false,
                    ],
                    [
                        'permissions' => [
                            BaseLevelVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW],
                            CustomerVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW, PermissionAccess::MODIFY],
                        ],
                        'expected' => true,
                    ],
                ],
                'id' => BaseLevelVoterTest::LEVEL_ID,
            ],
        ];

        $voter = new LevelVoter();

        $this->assertVoterAttributes($voter, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubjectById($id)
    {
        $level = $this->getMockBuilder(Level::class)->disableOriginalConstructor()->getMock();
        $level->method('getLevelId')->willReturn(new LevelId($id));

        return $level;
    }
}
