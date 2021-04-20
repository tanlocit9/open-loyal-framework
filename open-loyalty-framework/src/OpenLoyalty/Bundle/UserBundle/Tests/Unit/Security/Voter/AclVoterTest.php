<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\AclVoter;

/**
 * Class AclVoterTest.
 */
class AclVoterTest extends BaseVoterTest
{
    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            AclVoter::VIEW => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => '1'],
            AclVoter::EDIT => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => '1'],
            AclVoter::CREATE_ROLE => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => ''],
            AclVoter::LIST => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => ''],
        ];

        $voter = new AclVoter();

        $this->assertVoterAttributes($voter, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubjectById($id)
    {
        $role = $this->getMockBuilder(Role::class)->disableOriginalConstructor()->getMock();
        $role->method('getId')->willReturn(1);

        return $role;
    }
}
