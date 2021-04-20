<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Service;

use Broadway\ReadModel\Repository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class CustomerProvider.
 */
class CustomerProvider extends UserProvider implements UserProviderInterface
{
    /**
     * @var Repository
     */
    protected $customerDetailsRepository;

    /**
     * CustomerProvider constructor.
     *
     * @param EntityManager $em
     * @param Repository    $customerDetailsRepository
     */
    public function __construct(EntityManager $em, Repository $customerDetailsRepository)
    {
        parent::__construct($em);
        $this->customerDetailsRepository = $customerDetailsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        try {
            $user = $this->loadUserByUsernameOrEmail($username, Customer::class);
            if ($user instanceof Customer) {
                return $user;
            }
        } catch (UsernameNotFoundException $e) {
            // do nothing
        }

        try {
            $user = $this->loadUserByLoyaltyCardNumber($username);
            if ($user instanceof Customer) {
                return $user;
            }
        } catch (UsernameNotFoundException $e) {
            // do nothing
        }

        $user = $this->loadUserByPhoneNumber($username);
        if ($user instanceof Customer) {
            return $user;
        }
    }

    /**
     * @param string $number
     *
     * @return User
     */
    public function loadUserByLoyaltyCardNumber(string $number): User
    {
        $customers = $this->customerDetailsRepository->findBy(['loyaltyCardNumber' => $number]);
        if (count($customers) > 0) {
            /** @var CustomerDetails $customer */
            $customer = reset($customers);

            return $this->findUserByCustomerId($customer->getCustomerId());
        }

        throw new UsernameNotFoundException();
    }

    /**
     * @param string $number
     *
     * @return User
     */
    public function loadUserByPhoneNumber(string $number): User
    {
        $customers = $this->customerDetailsRepository->findBy(['phone' => $number]);
        if (count($customers) > 0) {
            /** @var CustomerDetails $customer */
            $customer = reset($customers);

            return $this->findUserByCustomerId($customer->getCustomerId());
        }

        throw new UsernameNotFoundException();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Heal\SecurityBundle\Entity\Customer';
    }

    /**
     * @param CustomerId $customerId
     *
     * @return User
     *
     * @throws UsernameNotFoundException
     */
    protected function findUserByCustomerId(CustomerId $customerId): User
    {
        try {
            /** @var QueryBuilder $qb */
            $qb = $this->em->createQueryBuilder();
            $qb->select('u')->from('OpenLoyaltyUserBundle:User', 'u');
            $qb->andWhere('u.id = :id')->setParameter(':id', (string) $customerId);
            $qb->andWhere('u.isActive = :true')->setParameter(':true', true);
            $user = $qb->getQuery()->getOneOrNullResult();

            if ($user instanceof User) {
                return $user;
            }
        } catch (\Exception $e) {
        }

        throw new UsernameNotFoundException();
    }
}
