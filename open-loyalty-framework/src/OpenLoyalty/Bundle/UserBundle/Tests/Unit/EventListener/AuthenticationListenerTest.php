<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\EventListener;

use Broadway\EventDispatcher\EventDispatcher;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Bundle\UserBundle\Entity\Permission;
use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use OpenLoyalty\Bundle\UserBundle\EventListener\AuthenticationListener;
use OpenLoyalty\Bundle\UserBundle\Service\UserManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AuthenticationListenerTest.
 */
final class AuthenticationListenerTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_payload_for_jwt_token(): void
    {
        $role1 = new Role('ROLE1');
        $role1->setMaster(true);
        $permission1 = new Permission('AREA_1', 'VIEW');
        $permission2 = new Permission('AREA_1', 'MODIFY');
        $permission3 = new Permission('AREA_2', 'VIEW');
        $role1->addPermission($permission1);
        $role1->addPermission($permission2);
        $role1->addPermission($permission3);

        $role2 = new Role('ROLE2');
        $permission1 = new Permission('AREA_1', 'VIEW');
        $permission2 = new Permission('AREA_1', 'MODIFY');
        $permission3 = new Permission('AREA_2', 'VIEW');
        $role2->addPermission($permission1);
        $role2->addPermission($permission2);
        $role2->addPermission($permission3);

        $user = new Admin(1);
        $user->setLastLoginAt(\DateTime::createFromFormat(\DateTime::ISO8601, '2018-11-27T10:34:45+0100'));
        $user->addRole($role1);
        $user->addRole($role2);
        $jwtEvent = $this->getMockBuilder(JWTCreatedEvent::class)
            ->setConstructorArgs([[], $user])
            ->setMethodsExcept(['getUser'])
            ->getMock();

        $authListener = new AuthenticationListener(
            $this->createMock(UserManager::class),
            $this->createMock(EventDispatcher::class),
            $this->createMock(TranslatorInterface::class)
        );

        $jwtEvent->expects($this->once())->method('setData')->with([
            'roles' => [
                'ROLE1',
                'ROLE2',
            ],
            'id' => 1,
            'superAdmin' => true,
            'lastLoginAt' => '2018-11-27T10:34:45+0100',
            'permissions' => [
                'AREA_1' => [
                    'VIEW',
                    'MODIFY',
                ],
                'AREA_2' => [
                    'VIEW',
                ],
            ],
        ]);

        $authListener->onJWTCreated($jwtEvent);
    }
}
