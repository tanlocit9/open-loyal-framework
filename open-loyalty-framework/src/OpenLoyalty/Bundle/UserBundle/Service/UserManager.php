<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use OpenLoyalty\Bundle\ActivationCodeBundle\Service\ActionTokenManager;
use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Entity\Seller;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Infrastructure\Repository\CustomerDetailsElasticsearchRepository;
use OpenLoyalty\Component\Seller\Domain\SellerId;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserManager.
 */
class UserManager
{
    /**
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PasswordGenerator
     */
    protected $passwordGenerator;

    /**
     * @var ActionTokenManager
     */
    protected $activationMethodProvider;

    /**
     * @var CustomerDetailsElasticsearchRepository
     */
    protected $customerDetailsRepository;

    /**
     * @var AclManagerInterface
     */
    private $aclManager;

    /**
     * UserManager constructor.
     *
     * @param UserPasswordEncoderInterface           $passwordEncoder
     * @param EntityManager                          $em
     * @param PasswordGenerator                      $passwordGenerator
     * @param ActionTokenManager                     $activationMethodProvider
     * @param CustomerDetailsElasticsearchRepository $customerDetailsRepository
     * @param AclManagerInterface                    $aclManager
     */
    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManager $em,
        PasswordGenerator $passwordGenerator,
        ActionTokenManager $activationMethodProvider,
        CustomerDetailsElasticsearchRepository $customerDetailsRepository,
        AclManagerInterface $aclManager
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
        $this->passwordGenerator = $passwordGenerator;
        $this->activationMethodProvider = $activationMethodProvider;
        $this->customerDetailsRepository = $customerDetailsRepository;
        $this->aclManager = $aclManager;
    }

    public function updateUser(User $user, $andFlush = true)
    {
        $this->updatePassword($user);
        $this->em->persist($user);
        if ($andFlush) {
            $this->em->flush();
        }
    }

    public function updatePassword(User $user)
    {
        if (0 !== strlen($password = $user->getPlainPassword())) {
            $encoder = $this->getEncoder($user);
            $user->setPassword($encoder->encodePassword($user, $password));
            $user->eraseCredentials();
        }
    }

    public function isCustomerExist($email)
    {
        return $this->em->getRepository('OpenLoyaltyUserBundle:Customer')
            ->findOneBy(['email' => strtolower($email)]) ? true : false;
    }

    public function isSellerExist($email)
    {
        return $this->em->getRepository('OpenLoyaltyUserBundle:Seller')
            ->findOneBy(['email' => strtolower($email)]) ? true : false;
    }

    /**
     * @param CustomerId  $customerId
     * @param null|string $email
     * @param null|string $password
     * @param null|string $phone
     *
     * @return Customer
     *
     * @throws \Exception
     */
    public function createNewCustomer(
        CustomerId $customerId,
        ?string $email = null,
        ?string $password = null,
        ?string $phone = null
    ) {
        $user = new Customer($customerId);
        if (null !== $email) {
            $user->setEmail($email);
        }
        $sendTemporaryPassword = false;

        if (!$password) {
            $user->setTemporaryPasswordSetAt(new \DateTime());
            $password = $this->passwordGenerator->generate();
            $sendTemporaryPassword = true;
        }
        $user->setPlainPassword($password);
        $user->setPhone($phone);
        $role = $this->em->getRepository('OpenLoyaltyUserBundle:Role')->findOneBy(['role' => 'ROLE_PARTICIPANT']);
        if ($role) {
            $user->addRole($role);
        }
        $this->updateUser($user);

        if ($sendTemporaryPassword) {
            $customerDetails = $this->customerDetailsRepository->find($user->getId());
            $this->activationMethodProvider->sendTemporaryPassword(
                $customerDetails,
                $user->getPlainPassword()
            );
        }

        return $user;
    }

    public function findUserByUsernameOrEmail($username, $class = User::class)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')->from($class, 'u');
        $qb->andWhere('u.username = :username or u.email = :username')->setParameter(':username', $username);
        $qb->andWhere('u.isActive = :true')->setParameter('true', true);
        $qb->andWhere('u.deletedAt is NULL');
        $user = $qb->getQuery()->getOneOrNullResult();

        if (!$user instanceof User) {
            return;
        }

        return $user;
    }

    public function findUserByConfirmationToken($token)
    {
        return $this->em->getRepository('OpenLoyaltyUserBundle:User')->findOneBy(['confirmationToken' => $token]);
    }

    public function createNewSeller(SellerId $sellerId, $email, $password, $allowPointTransfer = false)
    {
        $user = new Seller($sellerId);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setAllowPointTransfer($allowPointTransfer);
        $role = $this->em->getRepository('OpenLoyaltyUserBundle:Role')->findOneBy(['role' => 'ROLE_SELLER']);
        if ($role) {
            $user->addRole($role);
        }
        $this->updateUser($user);

        return $user;
    }

    /**
     * @param string $id
     * @param bool   $master
     *
     * @return Admin
     *
     * @throws \Exception
     */
    public function createNewAdmin(string $id, bool $master = false): Admin
    {
        $user = new Admin($id);

        if ($master) {
            $user->addRole($this->aclManager->getAdminMasterRole());
        }

        return $user;
    }

    /**
     * @param User $user
     *
     * @return UserPasswordEncoderInterface
     */
    protected function getEncoder(User $user)
    {
        return $this->passwordEncoder;
    }
}
