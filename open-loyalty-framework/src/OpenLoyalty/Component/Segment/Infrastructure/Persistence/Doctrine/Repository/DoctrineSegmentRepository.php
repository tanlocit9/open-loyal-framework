<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortByFilter;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortFilter;
use OpenLoyalty\Component\Segment\Domain\Segment;
use OpenLoyalty\Component\Segment\Domain\SegmentId;
use OpenLoyalty\Component\Segment\Domain\SegmentRepository;

/**
 * Class DoctrineSegmentRepository.
 */
class DoctrineSegmentRepository extends EntityRepository implements SegmentRepository
{
    use SortFilter, SortByFilter;

    /**
     * {@inheritdoc}
     */
    public function byId(SegmentId $segmentId)
    {
        return parent::find($segmentId);
    }

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
    public function findAllActive($returnQueryBuilder = false)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.active = :true')->setParameter('true', true);

        if ($returnQueryBuilder) {
            return $qb;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function save(Segment $segment)
    {
        $this->getEntityManager()->persist($segment);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Segment $segment)
    {
        $this->getEntityManager()->remove($segment);
        $this->getEntityManager()->flush($segment);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = null, $direction = 'ASC', $onlyActive = false)
    {
        $qb = $this->createQueryBuilder('l');

        if ($onlyActive) {
            $qb->andWhere('l.active = :true')->setParameter('true', true);
        }

        if ($sortField) {
            $qb->orderBy(
                'l.'.$this->validateSort($sortField),
                $this->validateSortBy($direction)
            );
        }

        $qb->setMaxResults($perPage);
        $qb->setFirstResult(($page - 1) * $perPage);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countTotal()
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select('count(l.segmentId)');

        return $qb->getQuery()->getSingleScalarResult();
    }
}
