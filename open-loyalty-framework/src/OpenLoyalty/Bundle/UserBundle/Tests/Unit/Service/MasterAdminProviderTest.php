<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Service;

use Doctrine\ORM\EntityManager;
use OpenLoyalty\Bundle\ActivationCodeBundle\Service\ActionTokenManager;
use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use OpenLoyalty\Bundle\UserBundle\Service\AclManagerInterface;
use OpenLoyalty\Bundle\UserBundle\Service\PasswordGenerator;
use OpenLoyalty\Bundle\UserBundle\Service\UserManager;
use OpenLoyalty\Component\Customer\Infrastructure\Repository\CustomerDetailsElasticsearchRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class MasterAdminProviderTest.
 */
final class MasterAdminProviderTest extends TestCase
{
    /**
     * @return UserManager
     *
     * @throws \ReflectionException
     */
    protected function getUserManager(): UserManager
    {
        $role = new Role('MASTER_ROLE');

        $aclManager = $this->getMockBuilder(AclManagerInterface::class)->getMock();
        $aclManager->method('getAdminMasterRole')->willReturn($role);
        $userManager = new UserManager(
            $this->createMock(UserPasswordEncoderInterface::class),
            $this->createMock(EntityManager::class),
            $this->createMock(PasswordGenerator::class),
            $this->createMock(ActionTokenManager::class),
            $this->createMock(CustomerDetailsElasticsearchRepository::class),
            $aclManager
        );

        return $userManager;
    }

    /**
     * @test
     */
    public function it_creates_new_admin_without_roles(): void
    {
        $userManager = $this->getUserManager();
        $admin = $userManager->createNewAdmin('1');

        $this->assertEquals(0, count($admin->getRoles()));
    }

    /**
     * @test
     */
    public function it_creates_new_admin_with_master_role(): void
    {
        $userManager = $this->getUserManager();
        $admin = $userManager->createNewAdmin('1', true);

        $this->assertEquals(1, count($admin->getRoles()));
        $this->assertEquals('MASTER_ROLE', $admin->getRoles()[0]->getRole());
    }
}
