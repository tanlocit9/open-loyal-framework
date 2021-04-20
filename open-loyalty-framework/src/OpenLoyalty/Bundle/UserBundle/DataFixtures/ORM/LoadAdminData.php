<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadAdminData.
 */
class LoadAdminData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    const ADMIN_ID = '22200000-0000-474c-b092-b0dd880c07e2';

    const ADMIN_USERNAME = 'admin';
    const ADMIN_PASSWORD = 'open';

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
    public function load(ObjectManager $manager): void
    {
        $superAdmin = $this->createAdmin(
            self::ADMIN_ID,
            self::ADMIN_USERNAME,
            'admin@oloy.com',
            self::ADMIN_PASSWORD,
            $this->getReference('role_admin')
        );

        $manager->persist($superAdmin);
        $this->addReference('user-admin', $superAdmin);

        $reporterAdmin = $this->createAdmin(
            'e21682f9-9ffc-4227-b4d6-ae7b41519f02',
            'reporter',
            'admin_reporter@oloy.com',
            self::ADMIN_PASSWORD,
            $this->getReference('reporter_role_admin')
        );
        $manager->persist($reporterAdmin);

        $supervisorAdmin = $this->createAdmin(
            'e21682f9-9ffc-4227-b4d6-ae7b41519f03',
            'supervisor',
            'supervisor@oloy.com',
            self::ADMIN_PASSWORD,
            $this->getReference('full_role_admin')
        );
        $manager->persist($supervisorAdmin);

        $manager->flush();
    }

    /**
     * @param string $id
     * @param string $userName
     * @param string $email
     * @param string $plainPassword
     * @param Role   $role
     *
     * @return Admin
     *
     * @throws \Exception
     */
    protected function createAdmin(string $id, string $userName, string $email, string $plainPassword, Role $role): Admin
    {
        $user = new Admin($id);
        $user->setPlainPassword($plainPassword);
        $user->setEmail($email);
        $password = $this->container->get('security.password_encoder')
            ->encodePassword($user, $user->getPlainPassword());

        $user->addRole($role);
        $user->setUsername($userName);
        $user->setPassword($password);
        $user->setIsActive(true);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder(): int
    {
        return 1;
    }
}
