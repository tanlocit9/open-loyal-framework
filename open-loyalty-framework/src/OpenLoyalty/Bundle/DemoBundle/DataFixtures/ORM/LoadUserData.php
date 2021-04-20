<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Seller\Domain\Command\ActivateSeller;
use OpenLoyalty\Component\Seller\Domain\Command\RegisterSeller;
use OpenLoyalty\Component\Seller\Domain\PosId;
use OpenLoyalty\Component\Seller\Domain\SellerId;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use OpenLoyalty\Bundle\UserBundle\Entity\Seller;

class LoadUserData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    const ADMIN_USERNAME = 'admin';
    const ADMIN_PASSWORD = 'open';
    const TEST_USER_ID = '00000000-0000-474c-b092-b0dd880c07e2';
    const TEST_SELLER_ID = '00000000-0000-474c-b092-b0dd880c07e4';

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $generator = $this->container->get('broadway.uuid.generator');

        $user = new Admin(new CustomerId($generator->generate()));
        $user->setPlainPassword(static::ADMIN_PASSWORD);
        $user->setEmail('admin@oloy.com');
        $password = $this->container->get('security.password_encoder')
            ->encodePassword($user, $user->getPlainPassword());

        $user->addRole($this->getReference('role_admin'));
        $user->setUsername($this::ADMIN_USERNAME);
        $user->setPassword($password);
        $user->setIsActive(true);

        $manager->persist($user);

        $this->addReference('user-admin', $user);

        $manager->flush();

        $this->loadSeller($manager);
    }

    protected function loadSeller(ObjectManager $manager)
    {
        $bus = $this->container->get('broadway.command_handling.command_bus');
        $faker = Factory::create();

        $bus->dispatch(
            new RegisterSeller(
                new SellerId(self::TEST_SELLER_ID),
                [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => 'merchant@openloyalty.io',
                    'phone' => $faker->e164PhoneNumber,
                    'posId' => new PosId(LoadPosData::POS3_ID),
                ]
            )
        );
        $bus->dispatch(new ActivateSeller(new SellerId(self::TEST_SELLER_ID)));
        $user = new Seller(new SellerId(self::TEST_SELLER_ID));
        $user->setEmail('merchant@openloyalty.io');
        $user->setIsActive(true);
        $user->addRole($this->getReference('role_seller'));
        $user->setPlainPassword('open');
        $this->container->get('oloy.user.user_manager')->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
