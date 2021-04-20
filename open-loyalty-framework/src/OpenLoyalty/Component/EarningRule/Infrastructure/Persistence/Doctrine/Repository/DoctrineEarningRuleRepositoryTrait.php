<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\Functions\Cast;

/**
 * Trait DoctrineEarningRuleRepositoryTrait.
 */
trait DoctrineEarningRuleRepositoryTrait
{
    /**
     * @return EntityManager
     */
    abstract protected function getEntityManager();

    /**
     * Creates a new QueryBuilder instance that is prepopulated for this entity name.
     *
     * @param string $alias
     * @param string $indexBy the index for the from
     *
     * @return QueryBuilder
     */
    abstract public function createQueryBuilder($alias, $indexBy = null);

    /**
     * @param array          $segmentIds
     * @param null           $levelId
     * @param \DateTime|null $date
     * @param null           $posId
     *
     * @return QueryBuilder
     *
     * @throws ORMException
     */
    protected function getEarningRulesForLevelAndSegmentQueryBuilder(
        array $segmentIds = [],
        $levelId = null,
        \DateTime $date = null,
        $posId = null
    ) {
        $this->getEntityManager()->getConfiguration()->addCustomStringFunction('cast', Cast::class);

        if (!$date) {
            $date = new \DateTime();
        }

        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.active = :true')->setParameter('true', true);
        $qb->andWhere($qb->expr()->orX(
            'e.allTimeActive = :true',
            'e.startAt <= :date AND e.endAt >= :date'
        ))->setParameter('date', $date);

        $levelOrSegment = $qb->expr()->orX();
        if ($levelId) {
            $levelId = ($levelId instanceof Identifier) ? $levelId->__toString() : $levelId;
            $levelOrSegment->add($qb->expr()->like('cast(e.levels as text)', ':levelId'));
            $qb->setParameter('levelId', '%'.$levelId.'%');
        }

        $i = 0;
        foreach ($segmentIds as $segmentId) {
            $segmentId = ($segmentId instanceof Identifier) ? $segmentId->__toString() : $segmentId;
            $levelOrSegment->add($qb->expr()->like('cast(e.segments as text)', ':segmentId'.$i));
            $qb->setParameter('segmentId'.$i, '%'.$segmentId.'%');
            ++$i;
        }

        $qb->andWhere($levelOrSegment);

        if ($posId) {
            // if posId is defined, find all ER that has this posId or has empty posId setting
            $pos = $qb->expr()->orX();
            $posId = ($posId instanceof Identifier) ? $posId->__toString() : $posId;
            $pos->add($qb->expr()->like('cast(e.pos as text)', ':posId'));
            $pos->add($qb->expr()->eq('cast(e.pos as text)', ':pos'));
            $qb->setParameter('posId', '%'.$posId.'%');
            $qb->setParameter('pos', '[]');
            $qb->andWhere($pos);
        } else {
            // if posId is not defined, find all ER that hs empty posId setting
            $qb->andWhere($qb->expr()->eq('cast(e.pos as text)', ':pos'))->setParameter('pos', '[]');
        }

        return $qb;
    }
}
