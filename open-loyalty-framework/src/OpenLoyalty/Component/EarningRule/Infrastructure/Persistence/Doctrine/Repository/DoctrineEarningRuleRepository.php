<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortByFilter;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortFilter;
use OpenLoyalty\Component\EarningRule\Domain\CustomEventEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleRepository;
use OpenLoyalty\Component\EarningRule\Domain\EventEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\ReferralEarningRule;

/**
 * Class DoctrineEarningRuleRepository.
 */
class DoctrineEarningRuleRepository extends EntityRepository implements EarningRuleRepository
{
    use SortFilter, SortByFilter, DoctrineEarningRuleRepositoryTrait;

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
    public function byId(EarningRuleId $earningRuleId)
    {
        return parent::find($earningRuleId);
    }

    /**
     * {@inheritdoc}
     */
    public function save(EarningRule $earningRule)
    {
        $this->getEntityManager()->persist($earningRule);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(EarningRule $earningRule)
    {
        $this->getEntityManager()->remove($earningRule);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = null, $direction = 'ASC', $returnQb = false)
    {
        $qb = $this->createQueryBuilder('e');

        if ($sortField) {
            $qb->orderBy(
                'e.'.$this->validateSort($sortField),
                $this->validateSortBy($direction)
            );
        }

        $qb->setMaxResults($perPage);
        $qb->setFirstResult(($page - 1) * $perPage);

        return $returnQb ? $qb : $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByParametersPaginated(array $params, $page = 1, $perPage = 10, $sortField = null, $direction = 'ASC', $returnQb = false)
    {
        $qb = $this->getByParamsQueryBuilder($params);

        if ($sortField) {
            $qb->orderBy(
                'e.'.$this->validateSort($sortField),
                $this->validateSortBy($direction)
            );
        }

        $qb->setMaxResults($perPage);
        $qb->setFirstResult(($page - 1) * $perPage);

        return $returnQb ? $qb : $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByParameters(array $params, $sortField = null, $direction = 'ASC', $returnQb = false)
    {
        $qb = $this->getByParamsQueryBuilder($params);

        if ($sortField) {
            $qb->orderBy(
                'e.'.$this->validateSort($sortField),
                $this->validateSortBy($direction)
            );
        }

        return $returnQb ? $qb : $qb->getQuery()->getResult();
    }

    /**
     * @param array $params
     *
     * @return QueryBuilder
     */
    public function countFindByParameters(array $params): QueryBuilder
    {
        $qb = $this->getByParamsQueryBuilder($params);

        try {
            $qb->select('count(e.earningRuleId)');

            return $qb;
        } catch (ORMException $ex) {
            return 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countTotal($returnQb = false)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('count(e.earningRuleId)');

        return $returnQb ? $qb : $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllActive(\DateTime $date = null)
    {
        if (!$date) {
            $date = new \DateTime();
        }

        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.active = :true')->setParameter('true', true);
        $qb->andWhere($qb->expr()->orX(
            'e.allTimeActive = :true',
            'e.startAt <= :date AND e.endAt >= :date'
        ))->setParameter('date', $date);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllActiveEventRules(
        $eventName = null,
        array $segmentIds = [],
        $levelId = null,
        \DateTime $date = null,
        $posId = null
    ) {
        $qb = $this->getEarningRulesForLevelAndSegmentQueryBuilder($segmentIds, $levelId, $date, $posId);

        $qb->add('from', EventEarningRule::class.' e');
        if ($eventName) {
            $qb->andWhere('e.eventName = :eventName')->setParameter('eventName', $eventName);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByCustomEventName(
        $eventName,
        array $segmentIds = [],
        $levelId = null,
        \DateTime $date = null,
        $posId = null
    ) {
        $qb = $this->getEarningRulesForLevelAndSegmentQueryBuilder($segmentIds, $levelId, $date, $posId);

        $qb->add('from', CustomEventEarningRule::class.' e');
        $qb->andWhere('e.eventName = :eventName')->setParameter('eventName', $eventName);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findReferralByEventName(
        $eventName,
        array $segmentIds = [],
        $levelId = null,
        \DateTime $date = null,
        $posId = null
    ) {
        $qb = $this->getEarningRulesForLevelAndSegmentQueryBuilder($segmentIds, $levelId, $date, $posId);

        $qb->add('from', ReferralEarningRule::class.' e');
        $qb->andWhere('e.eventName = :eventName')->setParameter('eventName', $eventName);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function isCustomEventEarningRuleExist($eventName, $currentEarningRuleId = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()->select('count(e)')
            ->from(CustomEventEarningRule::class, 'e');
        if ($currentEarningRuleId) {
            $qb->andWhere('e.earningRuleId != :earning_rule_id')
                ->setParameter('earning_rule_id', $currentEarningRuleId);
        }
        $qb->andWhere('e.eventName = :event_name')->setParameter('event_name', $eventName);

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllActiveEventRulesBySegmentsAndLevels(
        \DateTime $date = null,
        array $segmentIds = [],
        $levelId = null,
        $posId = null
    ) {
        $qb = $this->getEarningRulesForLevelAndSegmentQueryBuilder($segmentIds, $levelId, $date, $posId);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $params
     *
     * @return QueryBuilder
     */
    protected function getByParamsQueryBuilder(array $params): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder()->select('e');

        if (array_key_exists('type', $params) && !is_null($params['type'])) {
            $qb = $this->getEntityManager()->createQueryBuilder()->select('e');
            $qb->select('e')->from(EarningRule::TYPE_MAP[$params['type']], 'e');
        } else {
            $qb->select('e')->from(EarningRule::class, 'e');
        }

        return $qb;
    }
}
