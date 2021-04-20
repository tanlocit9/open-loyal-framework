<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Entity;

use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Bundle\UserBundle\Entity\Permission;
use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use PHPUnit\Framework\TestCase;

/**
 * Class UserTest.
 */
final class UserTest extends TestCase
{
    /**
     * @test
     */
    public function it_checks_if_user_is_superadmin_when_has_master_role(): void
    {
        $role = new Role('ROLE', 'role name', true);

        $user = new Admin('');
        $user->addRole($role);

        $this->assertTrue($user->isSuperAdmin());
    }

    /**
     * @test
     */
    public function it_checks_if_user_is_not_superadmin(): void
    {
        $role = new Role('ROLE');

        $user = new Admin('');
        $user->addRole($role);

        $this->assertFalse($user->isSuperAdmin());
    }

    /**
     * @test
     */
    public function it_checks_permissions(): void
    {
        $role = new Role('ROLE');
        $permission1 = new Permission('AREA_1', 'VIEW');
        $permission2 = new Permission('AREA_1', 'MODIFY');
        $permission3 = new Permission('AREA_2', 'VIEW');

        $role->addPermission($permission1);
        $role->addPermission($permission2);
        $role->addPermission($permission3);

        $user = new Admin('');
        $user->addRole($role);

        $this->assertTrue($user->hasPermission('AREA_1', ['VIEW']));
        $this->assertTrue($user->hasPermission('AREA_1', ['VIEW', 'MODIFY']));
        $this->assertFalse($user->hasPermission('AREA_2', ['VIEW', 'MODIFY']));
        $this->assertFalse($user->hasPermission('AREA_2', ['NOT_EXISTS']));
    }

    /**
     * @test
     */
    public function it_allows_when_user_has_modify_access_but_wants_to_view(): void
    {
        $role = new Role('ROLE');
        $permission1 = new Permission('AREA_1', 'MODIFY');

        $role->addPermission($permission1);

        $user = new Admin('');
        $user->addRole($role);

        $this->assertTrue($user->hasPermission('AREA_1', ['VIEW']));
        $this->assertTrue($user->hasPermission('AREA_1', ['MODIFY']));
        $this->assertFalse($user->hasPermission('AREA_1', ['NOT_EXISTS']));
    }
}
