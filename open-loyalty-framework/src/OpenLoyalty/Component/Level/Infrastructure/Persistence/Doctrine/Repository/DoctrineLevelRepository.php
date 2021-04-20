<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Level\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortByFilter;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortFilter;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\LevelRepository;

/**
 * Class DoctrineLevelRepository.
 */
class DoctrineLevelRepository extends EntityRepository implements LevelRepository
{
    use SortFilter, SortByFilter;

    /**
     * {@inheritdoc}
     */
    public function byId(LevelId $levelId)
    {
        return parent::find($levelId);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByRewardPercent($percent)
    {
        return $this->findOneBy(['reward.value' => $percent / 100]);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return parent::findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllActive()
    {
        $qb = $this->createQueryBuilder('l');
        $qb->andWhere('l.active = :true')->setParameter('true', true);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function save(Level $level)
    {
        $this->getEntityManager()->persist($level);
        $level->mergeNewTranslations();
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Level $level)
    {
        $this->getEntityManager()->remove($level);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = null, $direction = 'ASC')
    {
        $qb = $this->createQueryBuilder('l');

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
    public function findActivePaginated(?int $page = 1, ?int $perPage = 10, ?string $sortField = null, ?string $direction = 'ASC')
    {
        $qb = $this->createQueryBuilder('l');

        $qb->andWhere('l.active = :true')->setParameter('true', true);

        if ($sortField) {
            $qb->orderBy(
                sprintf('l.%s', $this->validateSort($sortField)),
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
        $qb->select('count(l.levelId)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findLevelByConditionValueWithTheBiggestReward($conditionValue)
    {
        $qb = $this->createQueryBuilder('l');
        $qb->andWhere('l.conditionValue <= :condVal')->setParameter('condVal', $conditionValue);
        $qb->andWhere('l.active = :true')->setParameter('true', true);
        $qb->orderBy('l.conditionValue', 'DESC');
        $qb->addOrderBy('l.reward.value', 'DESC');
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findNextLevelByConditionValueWithTheBiggestReward($conditionValue, $currentLevelValue)
    {
        $qb = $this->createQueryBuilder('l');
        $qb->andWhere('l.conditionValue > :condVal')->setParameter('condVal', $conditionValue);
        $qb->andWhere('l.active = :true')->setParameter('true', true);
        $qb->andWhere('l.conditionValue > :currentValue')->setParameter('currentValue', $currentLevelValue);
        $qb->orderBy('l.conditionValue', 'ASC');
        $qb->addOrderBy('l.reward.value', 'DESC');
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findPreviousLevelByConditionValueWithTheBiggestReward($conditionValue, $currentLevelValue)
    {
        $qb = $this->createQueryBuilder('l');
        $qb->andWhere('l.conditionValue >= :condVal')->setParameter('condVal', $conditionValue);
        $qb->andWhere('l.active = :true')->setParameter('true', true);
        $qb->andWhere('l.conditionValue < :currentValue')->setParameter('currentValue', $currentLevelValue);
        $qb->orderBy('l.conditionValue', 'ASC');
        $qb->addOrderBy('l.reward.value', 'DESC');
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
