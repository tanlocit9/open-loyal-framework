<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OpenLoyalty\Bundle\UserBundle\Entity\Permission;
use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use OpenLoyalty\Bundle\UserBundle\Model\AclAvailableObject;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Bundle\UserBundle\Service\AclManager;
use OpenLoyalty\Bundle\UserBundle\Service\AclManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadRoleData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $role = new Role('ROLE_USER');
        $manager->persist($role);
        $this->addReference('role_user', $role);

        $role = new Role('ROLE_PARTICIPANT');
        $manager->persist($role);
        $this->addReference('role_participant', $role);

        $role = new Role('ROLE_SELLER');
        $manager->persist($role);
        $this->addReference('role_seller', $role);

        $role = new Role('ROLE_ADMIN', 'Super admin', true);
        $manager->persist($role);
        $this->addReference('role_admin', $role);

        /** @var AclManagerInterface $aclManager */
        $aclManager = $this->container->get(AclManager::class);

        $role = new Role('ROLE_ADMIN', 'Reporter admin', false);
        /** @var AclAvailableObject $resource */
        foreach ($aclManager->getAvailableResources() as $resource) {
            $role->addPermission(new Permission($resource->getCode(), PermissionAccess::VIEW));
        }
        $manager->persist($role);
        $this->addReference('reporter_role_admin', $role);

        $role = new Role('ROLE_ADMIN', 'Full admin', false);
        /** @var AclAvailableObject $resource */
        foreach ($aclManager->getAvailableResources() as $resource) {
            $role->addPermission(new Permission($resource->getCode(), PermissionAccess::VIEW));
            $role->addPermission(new Permission($resource->getCode(), PermissionAccess::MODIFY));
        }
        $manager->persist($role);
        $this->addReference('full_role_admin', $role);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 0;
    }
}
