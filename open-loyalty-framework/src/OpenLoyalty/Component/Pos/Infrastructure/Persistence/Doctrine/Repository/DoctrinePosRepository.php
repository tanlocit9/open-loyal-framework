<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Pos\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortByFilter;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortFilter;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosId;
use OpenLoyalty\Component\Pos\Domain\PosRepository;

/**
 * Class DoctrinePosRepository.
 */
class DoctrinePosRepository extends EntityRepository implements PosRepository
{
    use SortFilter, SortByFilter;

    /**
     * {@inheritdoc}
     */
    public function findAll($returnQueryBuilder = false)
    {
        if ($returnQueryBuilder) {
            return $this->createQueryBuilder('e');
        }

        return parent::findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function byId(PosId $posId)
    {
        return parent::find($posId);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Pos $pos)
    {
        $this->getEntityManager()->persist($pos);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Pos $pos)
    {
        $this->getEntityManager()->remove($pos);
    }

    /**
     * {@inheritdoc}
     */
    public function oneByIdentifier($identifier)
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = null, $direction = 'ASC')
    {
        $qb = $this->createQueryBuilder('l');
        if ($page < 1) {
            $page = 1;
        }

        if ($sortField) {
            $qb->orderBy(
                'l.'.$this->validateSort($sortField),
                $this->validateSortBy($direction)
            );
        }
        if ($perPage) {
            $qb->setMaxResults($perPage);
            $qb->setFirstResult(($page - 1) * $perPage);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countTotal()
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select('count(l.posId)');

        return $qb->getQuery()->getSingleScalarResult();
    }
}
