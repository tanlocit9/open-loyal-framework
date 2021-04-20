<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Bundle\CoreBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\CampaignBundle\Security\Voter\CampaignVoter as BaseCampaignVoter;
use OpenLoyalty\Bundle\CoreBundle\Security\Voter\CampaignVoter;
use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\CustomerVoter;

/**
 * Class CampaignVoterTest.
 */
class CampaignVoterTest extends BaseVoterTest
{
    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            BaseCampaignVoter::LIST_ALL_VISIBLE_CAMPAIGNS => [
                'seller' => true,
                'customer' => false,
                'admin' => true,
                'admin_reporter' => true,
                'admin_custom' => [
                    [
                        'permissions' => [
                            BaseCampaignVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW],
                        ],
                        'expected' => false,
                    ],
                    [
                        'permissions' => [
                            BaseCampaignVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW],
                            CustomerVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW, PermissionAccess::MODIFY],
                        ],
                        'expected' => true,
                    ],
                ],
            ],
        ];

        $voter = new CampaignVoter();

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
