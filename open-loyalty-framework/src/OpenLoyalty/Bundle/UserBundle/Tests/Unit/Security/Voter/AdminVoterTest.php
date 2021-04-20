<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadAdminData;
use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\AdminVoter;

/**
 * Class AdminVoterTest.
 */
class AdminVoterTest extends BaseVoterTest
{
    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            AdminVoter::VIEW => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => ''],
            AdminVoter::EDIT => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => ''],
            AdminVoter::CREATE_USER => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => ''],
            AdminVoter::LIST => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => ''],
        ];

        $voter = new AdminVoter();

        $this->assertVoterAttributes($voter, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubjectById($id)
    {
        $admin = $this->getMockBuilder(Admin::class)->disableOriginalConstructor()->getMock();
        $admin->method('getId')->willReturn(LoadAdminData::ADMIN_ID);

        return $admin;
    }
}
