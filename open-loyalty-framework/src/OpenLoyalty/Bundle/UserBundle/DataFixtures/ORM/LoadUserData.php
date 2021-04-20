<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OpenLoyalty\Bundle\LevelBundle\DataFixtures\ORM\LoadLevelData;
use OpenLoyalty\Bundle\PosBundle\DataFixtures\ORM\LoadPosData;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Entity\Seller;
use OpenLoyalty\Bundle\UserBundle\Entity\Status;
use OpenLoyalty\Component\Customer\Domain\Command\ActivateCustomer;
use OpenLoyalty\Component\Customer\Domain\Command\AssignPosToCustomer;
use OpenLoyalty\Component\Customer\Domain\Command\MoveCustomerToLevel;
use OpenLoyalty\Component\Customer\Domain\Command\RegisterCustomer;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerAddress;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerDetails;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerLoyaltyCardNumber;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\LevelId;
use OpenLoyalty\Component\Seller\Domain\Command\ActivateSeller;
use OpenLoyalty\Component\Seller\Domain\Command\RegisterSeller;
use OpenLoyalty\Component\Seller\Domain\PosId;
use OpenLoyalty\Component\Seller\Domain\SellerId;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    const ADMIN_ID = '22200000-0000-474c-b092-b0dd880c07e2';
    const ADMIN_USERNAME = 'admin';
    const ADMIN_PASSWORD = 'open';

    const USER_USER_ID = '00000000-0000-474c-b092-b0dd880c07e1';
    const USER_USERNAME = 'user@oloy.com';
    const USER_PASSWORD = 'loyalty';
    const USER_PHONE_NUMBER = '+48234234000';
    const USER_LOYALTY_CARD_NUMBER = '47834433524';

    const USER1_USER_ID = '11111111-0000-474c-b092-b0dd880c07e1';
    const USER1_USERNAME = 'user-1@oloy.com';
    const USER1_PASSWORD = 'loyalty';
    const USER1_PHONE_NUMBER = '+48456456000';

    const USER2_USER_ID = '22222222-0000-474c-b092-b0dd880c07e1';
    const USER2_USERNAME = 'user-2@oloy.com';
    const USER2_PASSWORD = 'loyalty';
    const USER2_PHONE_NUMBER = '+48456457000';

    const USER3_USER_ID = '22222222-0000-474c-b092-b0dd880c07e2';
    const USER3_USERNAME = 'user-3@oloy.com';
    const USER3_PASSWORD = 'loyalty';
    const USER3_PHONE_NUMBER = '+48123456789';

    const TEST_USER_ID = '00000000-0000-474c-b092-b0dd880c07e2';
    const TEST_USERNAME = 'user-temp@oloy.com';
    const TEST_PASSWORD = 'loyalty';
    const TEST_USER_PHONE_NUMBER = '+48345345000';

    const TEST_RETURN_USER_ID = '11000000-0000-474c-b092-b0dd880c07e2';
    const TEST_RETURN_USERNAME = 'return@oloy.com';
    const TEST_RETURN_PASSWORD = 'return';
    const TEST_RETURN_USER_PHONE_NUMBER = '+48123123787';

    const TEST_SELLER_ID = '00000000-0000-474c-b092-b0dd880c07e4';
    const TEST_SELLER_USERNAME = 'john@doe.com';
    const TEST_SELLER_PASSWORD = 'open';

    const TEST_SELLER2_ID = '00000000-0000-474c-b092-b0dd880c07e5';
    const TEST_SELLER2_USERNAME = 'john2@doe2.com';
    const TEST_SELLER2_PASSWORD = 'open';

    const USER_COUPON_RETURN_ID = '00000000-0000-474c-b092-b0dd880c07aa';
    const USER_COUPON_RETURN_USERNAME = 'user-return@oloy.com';
    const USER_COUPON_RETURN_PASSWORD = 'loyalty';

    const USER_TRANSFER_1_USER_ID = '00000000-0000-474c-b092-b0dd880c07f3';
    const USER_TRANSFER_1_USERNAME = 'user-transfer-1@oloy.com';
    const USER_TRANSFER_1_PASSWORD = 'loyalty';

    const USER_TRANSFER_2_USER_ID = '00000000-0000-474c-b092-b0dd880c07f4';
    const USER_TRANSFER_2_USERNAME = 'user-transfer-2@oloy.com';
    const USER_TRANSFER_2_PASSWORD = 'loyalty';

    const USER_TRANSFER_3_USER_ID = '00000000-0000-474c-b092-b0dd880c07f5';
    const USER_TRANSFER_3_USERNAME = 'user-transfer-3@oloy.com';
    const USER_TRANSFER_3_PASSWORD = 'loyalty';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadCustomersData($manager);
        $this->loadForTransferData($manager);
        $this->loadForCouponReturnData($manager);
        $this->loadSeller($manager);
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    protected function loadSeller(ObjectManager $manager)
    {
        $bus = $this->container->get('broadway.command_handling.command_bus');

        $bus->dispatch(
            new RegisterSeller(
                new SellerId(self::TEST_SELLER_ID),
                [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => self::TEST_SELLER_USERNAME,
                    'phone' => '+48123123123',
                    'posId' => new PosId(LoadPosData::POS_ID),
                ]
            )
        );
        $bus->dispatch(new ActivateSeller(new SellerId(self::TEST_SELLER_ID)));
        $user = new Seller(new SellerId(self::TEST_SELLER_ID));
        $user->setEmail(self::TEST_SELLER_USERNAME);
        $user->setIsActive(true);
        $user->addRole($this->getReference('role_seller'));
        $user->setPlainPassword(self::TEST_SELLER_PASSWORD);
        $user->setAllowPointTransfer(true);
        $this->container->get('oloy.user.user_manager')->updateUser($user);

        $bus->dispatch(
            new RegisterSeller(
                new SellerId(self::TEST_SELLER2_ID),
                [
                    'firstName' => 'John2',
                    'lastName' => 'Doe2',
                    'email' => self::TEST_SELLER2_USERNAME,
                    'phone' => '+48123123124',
                    'posId' => new PosId(LoadPosData::POS2_ID),
                ]
            )
        );
        $bus->dispatch(new ActivateSeller(new SellerId(self::TEST_SELLER2_ID)));
        $user = new Seller(new SellerId(self::TEST_SELLER2_ID));
        $user->setEmail(self::TEST_SELLER2_USERNAME);
        $user->setIsActive(true);
        $user->addRole($this->getReference('role_seller'));
        $user->setPlainPassword(self::TEST_SELLER2_PASSWORD);
        $user->setAllowPointTransfer(false);
        $this->container->get('oloy.user.user_manager')->updateUser($user);
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    protected function loadCustomersData(ObjectManager $manager)
    {
        $bus = $this->container->get('broadway.command_handling.command_bus');

        // USER
        $customerId = new CustomerId(static::USER_USER_ID);
        $command = new RegisterCustomer(
            $customerId,
            $this->getDefaultCustomerData(
                'John',
                'Doe',
                $this::USER_USERNAME,
                $this::USER_PHONE_NUMBER
            )
        );

        $bus->dispatch($command);
        $bus->dispatch(new UpdateCustomerLoyaltyCardNumber($customerId, $this::USER_LOYALTY_CARD_NUMBER));
        $bus->dispatch(new ActivateCustomer($customerId));

        $user = new Customer($customerId);
        $user->setPlainPassword($this::USER_PASSWORD);
        $user->setPhone($command->getCustomerData()['phone']);

        $password = $this->container->get('security.password_encoder')
            ->encodePassword($user, $user->getPlainPassword());

        $user->addRole($this->getReference('role_participant'));
        $user->setPassword($password);
        $user->setIsActive(true);
        $user->setStatus(Status::typeActiveNoCard());

        $user->setEmail($this::USER_USERNAME);
        $manager->persist($user);
        $this->addReference('user-1', $user);

        // USER1
        $customerId = new CustomerId(static::USER1_USER_ID);
        $command = new RegisterCustomer(
            $customerId,
            $this->getDefaultCustomerData('John1', 'Doe1', $this::USER1_USERNAME, $this::USER1_PHONE_NUMBER)
        );

        $bus->dispatch($command);
        $bus->dispatch(new ActivateCustomer($customerId));

        $command = new UpdateCustomerDetails(
            $customerId,
            [
                'labels' => [
                    0 => [
                        'key' => 'test',
                        'value' => 'test',
                    ],
                ],
                'birthDate' => (new \DateTime())->setTimestamp(653011200),
                'phone' => $this::USER1_PHONE_NUMBER,
            ]
        );
        $bus->dispatch($command);

        $user = new Customer($customerId);
        $user->setPlainPassword($this::USER1_PASSWORD);
        $user->setPhone($command->getCustomerData()['phone']);

        $password = $this->container->get('security.password_encoder')
                                    ->encodePassword($user, $user->getPlainPassword());

        $user->addRole($this->getReference('role_participant'));
        $user->setPassword($password);
        $user->setIsActive(true);
        $user->setStatus(Status::typeActiveNoCard());

        $user->setEmail($this::USER1_USERNAME);
        $manager->persist($user);

        // USER2
        $customerId = new CustomerId(static::USER2_USER_ID);
        $command = new RegisterCustomer(
            $customerId,
            $this->getDefaultCustomerData('Alice', 'Smith', $this::USER2_USERNAME, $this::USER2_PHONE_NUMBER)
        );

        $bus->dispatch($command);
        $bus->dispatch(new ActivateCustomer($customerId));
        $bus->dispatch(new AssignPosToCustomer($customerId, new \OpenLoyalty\Component\Customer\Domain\PosId(LoadPosData::POS_ID)));

        $user = new Customer($customerId);
        $user->setPlainPassword($this::USER2_PASSWORD);
        $user->setPhone($command->getCustomerData()['phone']);

        $password = $this->container->get('security.password_encoder')
                                    ->encodePassword($user, $user->getPlainPassword());

        $user->addRole($this->getReference('role_participant'));
        $user->setPassword($password);
        $user->setIsActive(true);
        $user->setStatus(Status::typeActiveNoCard());

        $user->setEmail($this::USER2_USERNAME);
        $manager->persist($user);
        $this->addReference('user-2', $user);
        // USER3
        $customerId = new CustomerId(self::USER3_USER_ID);
        $command = new RegisterCustomer(
            $customerId,
            $this->getDefaultCustomerData('Jane', 'Fonda', self::USER3_USERNAME, self::USER3_PHONE_NUMBER)
        );

        $bus->dispatch($command);
        $bus->dispatch(new ActivateCustomer($customerId));
        $bus->dispatch(new AssignPosToCustomer($customerId, new \OpenLoyalty\Component\Customer\Domain\PosId(LoadPosData::POS_ID)));

        $user = new Customer($customerId);
        $user->setPlainPassword(self::USER3_PASSWORD);
        $user->setPhone($command->getCustomerData()['phone']);

        $password = $this->container->get('security.password_encoder')
            ->encodePassword($user, $user->getPlainPassword());

        $user->addRole($this->getReference('role_participant'));
        $user->setPassword($password);
        $user->setIsActive(true);
        $user->setStatus(Status::typeActiveNoCard());

        $user->setEmail(self::USER3_USERNAME);
        $manager->persist($user);

        // USER_TEST
        $customerId = new CustomerId(self::TEST_USER_ID);
        $command = new RegisterCustomer($customerId, $this->getDefaultCustomerData('Jane', 'Doe', self::TEST_USERNAME, self::TEST_USER_PHONE_NUMBER));
        $bus->dispatch($command);
        $bus->dispatch(new UpdateCustomerAddress($customerId, [
            'street' => 'Street',
            'address1' => '1',
            'postal' => '00-000',
            'city' => 'London',
            'province' => 'province',
            'country' => 'UK',
        ]));
        $bus->dispatch(new UpdateCustomerLoyaltyCardNumber($customerId, '0000'));
        $bus->dispatch(
            new MoveCustomerToLevel(
                $customerId,
                new LevelId(LoadLevelData::LEVEL1_ID),
                LoadLevelData::LEVEL0_NAME
            )
        );
        $bus->dispatch(new ActivateCustomer($customerId));

        $user = new Customer($customerId);
        $user->setPlainPassword($this::TEST_PASSWORD);

        $password = $this->container->get('security.password_encoder')
            ->encodePassword($user, $user->getPlainPassword());

        $user->addRole($this->getReference('role_participant'));
        $user->setPassword($password);
        $user->setIsActive(true);
        $user->setStatus(Status::typeActiveNoCard());

        $user->setEmail('user-temp@oloy.com');
        $user->setTemporaryPasswordSetAt(new \DateTime());

        $manager->persist($user);

        // Return test user

        $customerId = new CustomerId(static::TEST_RETURN_USER_ID);
        $command = new RegisterCustomer(
            $customerId,
            $this->getDefaultCustomerData('Jon', 'Returner', $this::TEST_RETURN_USERNAME, $this::TEST_RETURN_USER_PHONE_NUMBER)
        );

        $bus->dispatch($command);
        $bus->dispatch(new ActivateCustomer($customerId));

        $user = new Customer($customerId);
        $user->setPlainPassword($this::TEST_RETURN_PASSWORD);
        $user->setPhone($command->getCustomerData()['phone']);

        $password = $this->container->get('security.password_encoder')
            ->encodePassword($user, $user->getPlainPassword());

        $user->addRole($this->getReference('role_participant'));
        $user->setPassword($password);
        $user->setIsActive(true);
        $user->setStatus(Status::typeActiveNoCard());

        $user->setEmail($this::TEST_RETURN_USERNAME);
        $manager->persist($user);
        $this->addReference('return-user', $user);

        $manager->persist($user);
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    protected function loadForTransferData(ObjectManager $manager): void
    {
        $bus = $this->container->get('broadway.command_handling.command_bus');
        $users = [
            [static::USER_TRANSFER_1_USER_ID, static::USER_TRANSFER_1_USERNAME, static::USER_TRANSFER_1_PASSWORD],
            [static::USER_TRANSFER_2_USER_ID, static::USER_TRANSFER_2_USERNAME, static::USER_TRANSFER_2_PASSWORD],
            [static::USER_TRANSFER_3_USER_ID, static::USER_TRANSFER_3_USERNAME, static::USER_TRANSFER_3_PASSWORD],
        ];
        $i = 0;
        foreach ($users as $data) {
            $customerId = new CustomerId($data[0]);
            $command = new RegisterCustomer(
                $customerId,
                $this->getDefaultCustomerData(
                    'TestUser',
                    'ForTransfersTest',
                    $data[1],
                    '123123231231231236'.$i
                )
            );

            $bus->dispatch($command);
            $bus->dispatch(new ActivateCustomer($customerId));

            $user = new Customer($customerId);
            $user->setPlainPassword($data[2]);
            $user->setPhone($command->getCustomerData()['phone']);

            $password = $this->container->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());

            $user->addRole($this->getReference('role_participant'));
            $user->setPassword($password);
            $user->setIsActive(true);
            $user->setStatus(Status::typeActiveNoCard());

            $user->setEmail($data[1]);
            $manager->persist($user);
            ++$i;
        }
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    protected function loadForCouponReturnData(ObjectManager $manager): void
    {
        $bus = $this->container->get('broadway.command_handling.command_bus');
        $users = [
            [static::USER_COUPON_RETURN_ID, static::USER_COUPON_RETURN_USERNAME, static::USER_COUPON_RETURN_PASSWORD],
        ];
        $i = 0;
        foreach ($users as $data) {
            $customerId = new CustomerId($data[0]);
            $command = new RegisterCustomer(
                $customerId,
                $this->getDefaultCustomerData(
                    'TestUser',
                    'ForCouponTest',
                    $data[1],
                    '1231232312312312376'.$i
                )
            );

            $bus->dispatch($command);
            $bus->dispatch(new ActivateCustomer($customerId));

            $user = new Customer($customerId);
            $user->setPlainPassword($data[2]);
            $user->setPhone($command->getCustomerData()['phone']);

            $password = $this->container->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());

            $user->addRole($this->getReference('role_participant'));
            $user->setPassword($password);
            $user->setIsActive(true);
            $user->setStatus(Status::typeActiveNoCard());

            $user->setEmail($data[1]);
            $manager->persist($user);
            ++$i;
        }
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $phone
     *
     * @return array
     */
    public static function getDefaultCustomerData(
        $firstName,
        $lastName,
        $email,
        $phone = '00000'
    ) {
        return [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'gender' => 'male',
            'phone' => $phone,
            'email' => $email,
            'birthDate' => 653011200,
            'createdAt' => 1470646394,
            'company' => [
                'name' => 'test',
                'nip' => 'nip',
            ],
            'loyaltyCardNumber' => '000000',
            'address' => [
                'street' => 'Dmowskiego',
                'address1' => '21',
                'city' => 'Wrocław',
                'country' => 'pl',
                'postal' => '50-300',
                'province' => 'Dolnośląskie',
            ],
            'status' => [
                'type' => 'new',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
