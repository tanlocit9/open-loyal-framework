<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\CoreBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\CampaignBundle\Security\Voter\CampaignVoter;
use OpenLoyalty\Bundle\CoreBundle\Security\Voter\CustomerVoter;
use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\LevelBundle\Security\Voter\LevelVoter;
use OpenLoyalty\Bundle\PointsBundle\Security\Voter\PointsTransferVoter;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\CustomerVoter as BaseCustomerVoter;
use OpenLoyalty\Bundle\UserBundle\Tests\Unit\Security\Voter\CustomerVoterTest as BaseCustomerVoterTest;

/**
 * Class CustomerVoterTest.
 */
final class CustomerVoterTest extends BaseVoterTest
{
    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            BaseCustomerVoter::ASSIGN_CUSTOMER_LEVEL => [
                'seller' => false,
                'customer' => false,
                'admin' => true,
                'admin_reporter' => true,
                'id' => BaseCustomerVoterTest::CUSTOMER_ID,
                'admin_custom' => [
                    [
                        'permissions' => [
                            LevelVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW],
                        ],
                        'expected' => true,
                    ],
                    [
                        'permissions' => [],
                        'expected' => false,
                    ],
                ],
            ],
            PointsTransferVoter::ADD_POINTS => [
                'seller' => false,
                'customer' => false,
                'admin' => true,
                'admin_reporter' => false,
                'admin_custom' => [
                    [
                        'permissions' => [
                            BaseCustomerVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW, PermissionAccess::MODIFY],
                        ],
                        'expected' => true,
                    ],
                    [
                        'permissions' => [BaseCustomerVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW]],
                        'expected' => false,
                    ],
                ],
            ],
            PointsTransferVoter::TRANSFER_POINTS => [
                'seller' => false,
                'customer' => false,
                'admin' => true,
                'admin_reporter' => false,
                'admin_custom' => [
                    [
                        'permissions' => [
                            BaseCustomerVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW, PermissionAccess::MODIFY],
                        ],
                        'expected' => true,
                    ],
                    [
                        'permissions' => [BaseCustomerVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW]],
                        'expected' => false,
                    ],
                ],
            ],
            PointsTransferVoter::SPEND_POINTS => [
                'seller' => false,
                'customer' => false,
                'admin' => true,
                'admin_reporter' => false,
                'admin_custom' => [
                    [
                        'permissions' => [
                            BaseCustomerVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW, PermissionAccess::MODIFY],
                        ],
                        'expected' => true,
                    ],
                    [
                        'permissions' => [BaseCustomerVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW]],
                        'expected' => false,
                    ],
                ],
            ],
            CampaignVoter::VIEW_BUY_FOR_CUSTOMER_ADMIN => [
                'seller' => false,
                'customer' => false,
                'admin' => true,
                'admin_reporter' => true,
                'admin_custom' => [
                    [
                        'permissions' => [
                            BaseCustomerVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW],
                        ],
                        'expected' => true,
                    ],
                    [
                        'permissions' => [],
                        'expected' => false,
                    ],
                ],
            ],
            CampaignVoter::LIST_ALL_CAMPAIGNS_CUSTOMERS => [
                'seller' => false,
                'customer' => false,
                'admin' => true,
                'admin_reporter' => true,
                'admin_custom' => [
                    [
                        'permissions' => [
                            BaseCustomerVoter::PERMISSION_RESOURCE => [PermissionAccess::VIEW],
                        ],
                        'expected' => true,
                    ],
                    [
                        'permissions' => [],
                        'expected' => false,
                    ],
                ],
            ],
        ];

        $voter = new CustomerVoter();

        $this->assertVoterAttributes($voter, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubjectById($id)
    {
        $customer = $this->getMockBuilder(CustomerDetails::class)->disableOriginalConstructor()->getMock();
        $customer->method('getCustomerId')->willReturn(new CustomerId($id));

        return $customer;
    }
}
