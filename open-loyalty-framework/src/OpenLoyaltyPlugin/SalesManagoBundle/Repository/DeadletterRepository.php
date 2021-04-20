<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyaltyPlugin\SalesManagoBundle\Repository;

use Doctrine\ORM\EntityManager;
use OpenLoyaltyPlugin\SalesManagoBundle\Entity\Deadletter;

/**
 * DeadletterRepository.
 *
 * @category    DivanteOpenLoyalty
 *
 * @author      Michal Kajszczak <mkajszczak@divante.pl>
 * @copyright   Copyright (C) 2016 Divante Sp. z o.o.
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeadletterRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * DeadletterRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Deadletter $deadletter
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Deadletter $deadletter)
    {
        $this->entityManager->persist($deadletter);
        $this->entityManager->flush();
    }
}
