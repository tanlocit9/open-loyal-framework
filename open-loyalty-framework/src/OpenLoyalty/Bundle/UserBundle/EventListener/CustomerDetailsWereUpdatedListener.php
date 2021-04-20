<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\EventListener;

use Broadway\Repository\Repository;
use Doctrine\ORM\EntityManagerInterface;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer as CustomerEntity;
use OpenLoyalty\Bundle\UserBundle\Service\UserManager;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerUpdatedSystemEvent;

/**
 * Class CustomerDetailsWereUpdatedListener.
 */
class CustomerDetailsWereUpdatedListener
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Repository
     */
    private $customerRepository;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * CustomerDetailsWereUpdatedListener constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param Repository             $repository
     * @param UserManager            $userManager
     */
    public function __construct(EntityManagerInterface $entityManager, Repository $repository, UserManager $userManager)
    {
        $this->entityManager = $entityManager;
        $this->customerRepository = $repository;
        $this->userManager = $userManager;
    }

    /**
     * @param CustomerUpdatedSystemEvent $event
     */
    public function handle(CustomerUpdatedSystemEvent $event): void
    {
        /** @var Customer $customer */
        $customer = $this->customerRepository->load((string) $event->getCustomerId());

        $customerEntityRepository = $this->entityManager->getRepository(CustomerEntity::class);

        $customerEntity = $customerEntityRepository->find((string) $event->getCustomerId());

        if ($customer instanceof Customer && $customerEntity instanceof CustomerEntity) {
            $customerEntity->setPhone($customer->getPhone());
            $customerEntity->setEmail($customer->getEmail());
            $this->userManager->updateUser($customerEntity);
        }
    }
}
