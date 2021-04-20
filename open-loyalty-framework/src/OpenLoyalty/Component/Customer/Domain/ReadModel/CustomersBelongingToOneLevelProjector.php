<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\ReadModel;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Core\Infrastructure\Projector\Projector;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\CustomerRepository;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasMovedToLevel;
use OpenLoyalty\Component\Customer\Domain\LevelId as CustomerLevelId;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\LevelRepository;

/**
 * Class CustomersBelongingToOneLevelProjector.
 */
class CustomersBelongingToOneLevelProjector extends Projector
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var Repository
     */
    private $customersBelongingToOneLevelRepository;

    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * CustomersBelongingToOneLevelProjector constructor.
     *
     * @param CustomerRepository $customerRepository
     * @param Repository         $customersBelongingToOneLevelRepository
     * @param LevelRepository    $levelRepository
     */
    public function __construct(
        CustomerRepository $customerRepository,
        Repository $customersBelongingToOneLevelRepository,
        LevelRepository $levelRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->customersBelongingToOneLevelRepository = $customersBelongingToOneLevelRepository;
        $this->levelRepository = $levelRepository;
    }

    /**
     * @param CustomerWasMovedToLevel $event
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function applyCustomerWasMovedToLevel(CustomerWasMovedToLevel $event)
    {
        /** @var Customer $customer */
        $customer = $this->customerRepository->load((string) $event->getCustomerId());

        // Remove user from the old level
        $oldLevelId = $event->getOldLevelId();
        if ($oldLevelId) {
            $oldReadModel = $this->getReadModel($oldLevelId, false);
            if ($oldReadModel) {
                $oldReadModel->removeCustomer($event->getCustomerId());
                $this->customersBelongingToOneLevelRepository->save($oldReadModel);

                // Decrease level's customer count
                /** @var Level $level */
                $level = $this->levelRepository->byId(new LevelId((string) $oldLevelId));
                if ($level) {
                    $level->removeCustomer();
                    $this->levelRepository->save($level);
                }
            }
        }

        // Add user to the new level
        $levelId = $event->getLevelId();
        if ($levelId) {
            $readModel = $this->getReadModel($levelId);

            if (null === $readModel) {
                return;
            }

            $customerDetails = new CustomerDetails($event->getCustomerId());
            $customerDetails->setFirstName($customer->getFirstName());
            $customerDetails->setLastName($customer->getLastName());
            $customerDetails->setEmail($customer->getEmail());
            $readModel->addCustomer($customerDetails);

            $this->customersBelongingToOneLevelRepository->save($readModel);

            // Increase level's customer count
            /** @var Level $level */
            $level = $this->levelRepository->byId(new LevelId((string) $levelId));
            if ($level) {
                $level->addCustomer();
                $this->levelRepository->save($level);
            }
        }
    }

    /**
     * @param CustomerLevelId $levelId
     * @param bool            $createIfNull
     *
     * @return null|CustomersBelongingToOneLevel
     */
    private function getReadModel(CustomerLevelId $levelId, bool $createIfNull = true): ?CustomersBelongingToOneLevel
    {
        $readModel = $this->customersBelongingToOneLevelRepository->find((string) $levelId);

        if (null === $readModel && $createIfNull) {
            $readModel = new CustomersBelongingToOneLevel($levelId);
        }

        return $readModel;
    }
}
