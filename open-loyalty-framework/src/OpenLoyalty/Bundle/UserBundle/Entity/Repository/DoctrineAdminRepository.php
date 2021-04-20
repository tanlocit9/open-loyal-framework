<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Bundle\UserBundle\Exception\AdminNotFoundException;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortByFilter;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortFilter;

/**
 * Class DoctrineAdminRepository.
 */
class DoctrineAdminRepository extends EntityRepository implements AdminRepository
{
    use SortFilter;
    use SortByFilter;

    /**
     * {@inheritdoc}
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = null, $direction = 'ASC')
    {
        $qb = $this->createQueryBuilder('e');

        if ($sortField) {
            $qb->orderBy(
                'e.'.$this->validateSort($sortField),
                $this->validateSortBy($direction)
            );
        }

        $qb->addOrderBy('e.firstName', 'ASC');

        $qb->setMaxResults($perPage);
        $qb->setFirstResult(($page - 1) * $perPage);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countTotal()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('count(e.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmailExist($email, $excludedId = null)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->andWhere('u.email = :email')->setParameter('email', strtolower($email));
        if ($excludedId) {
            $qb->andWhere('u.id != :id')->setParameter('id', $excludedId);
        }

        $result = $qb->getQuery()->getResult();

        return count($result) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function findById(string $adminId): Admin
    {
        try {
            $qb = $this->createQueryBuilder('u');
            $qb->andWhere('u.id = :id')->setParameter('id', $adminId);

            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            throw new AdminNotFoundException();
        } catch (\Exception $e) {
            throw new AdminNotFoundException();
        }
    }
}
