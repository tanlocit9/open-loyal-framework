<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CoreBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Security\Voter\SellerVoter;
use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\PosBundle\Security\Voter\PosVoter;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\SellerVoter as BaseSellerVoter;

/**
 * Class SellerVoterTest.
 */
class SellerVoterTest extends BaseVoterTest
{
    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            BaseSellerVoter::ASSIGN_POS_TO_SELLER => [
                'seller' => false,
                'customer' => false,
                'admin' => true,
                'admin_reporter' => false,
                'admin_custom' => [
                    [
                        'permissions' => [
                            BaseSellerVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW],
                        ],
                        'expected' => false,
                    ],
                    [
                        'permissions' => [
                            BaseSellerVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW],
                            PosVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW],
                        ],
                        'expected' => false,
                    ],
                    [
                        'permissions' => [
                            BaseSellerVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW, PermissionAccess::MODIFY],
                            PosVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW],
                        ],
                        'expected' => true,
                    ],
                ],
            ],
        ];

        $voter = new SellerVoter();

        $this->assertVoterAttributes($voter, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubjectById($id)
    {
        return;
    }
}
