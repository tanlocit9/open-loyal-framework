<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use OpenLoyalty\Component\EarningRule\Domain\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleUsageId;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleUsageRepository;
use OpenLoyalty\Component\EarningRule\Domain\Model\UsageSubject;

/**
 * Class DoctrineEarningRuleUsageRepository.
 */
class DoctrineEarningRuleUsageRepository extends EntityRepository implements EarningRuleUsageRepository
{
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
    public function byId(EarningRuleUsageId $earningRuleUsageId)
    {
        return parent::find($earningRuleUsageId);
    }

    /**
     * {@inheritdoc}
     */
    public function countDailyUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int
    {
        $dayStart = new \DateTime();
        $dayStart->setTime(0, 0, 0);
        $dayEnd = new \DateTime();
        $dayEnd->setTime(23, 59, 59);

        return $this->findUsageByDates($earningRuleId, $subject, $dayStart, $dayEnd);
    }

    /**
     * {@inheritdoc}
     */
    public function countWeeklyUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int
    {
        $start = new \DateTime();
        $start->modify('monday this week');
        $start->setTime(0, 0, 0);
        $end = new \DateTime();
        $end->modify('sunday this week');
        $end->setTime(23, 59, 59);

        return $this->findUsageByDates($earningRuleId, $subject, $start, $end);
    }

    /**
     * {@inheritdoc}
     */
    public function countMonthlyUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int
    {
        $start = new \DateTime();
        $start->modify('first day of this month');
        $start->setTime(0, 0, 0);
        $end = new \DateTime();
        $end->modify('last day of this month');
        $end->setTime(23, 59, 59);

        return $this->findUsageByDates($earningRuleId, $subject, $start, $end);
    }

    /**
     * {@inheritdoc}
     */
    public function countThreeMonthlyUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int
    {
        $month = new \DateTime();
        $start = new \DateTime();
        $end = new \DateTime();

        if ($month->format('m') >= 1 && $month->format('m') <= 3) {
            $start->modify('first day of January');
            $end->modify('last day of March');
        }
        if ($month->format('m') >= 4 && $month->format('m') <= 6) {
            $start->modify('first day of April');
            $end->modify('last day of June');
        }
        if ($month->format('m') >= 7 && $month->format('m') <= 9) {
            $start->modify('first day of July');
            $end->modify('last day of September');
        }
        if ($month->format('m') >= 10 && $month->format('m') <= 12) {
            $start->modify('first day of October');
            $end->modify('last day of December');
        }

        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);

        return $this->findUsageByDates($earningRuleId, $subject, $start, $end);
    }

    /**
     * {@inheritdoc}
     */
    public function countSixMonthlyUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int
    {
        $month = new \DateTime();
        $start = new \DateTime();
        $end = new \DateTime();

        if ($month->format('m') >= 1 && $month->format('m') <= 6) {
            $start->modify('first day of January');
            $end->modify('last day of June');
        }
        if ($month->format('m') >= 7 && $month->format('m') <= 12) {
            $start->modify('first day of July');
            $end->modify('last day of December');
        }

        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);

        return $this->findUsageByDates($earningRuleId, $subject, $start, $end);
    }

    /**
     * {@inheritdoc}
     */
    public function countYearUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int
    {
        $start = new \DateTime();
        $end = new \DateTime();

        $start->modify('first day of January');
        $end->modify('last day of December');

        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);

        return $this->findUsageByDates($earningRuleId, $subject, $start, $end);
    }

    /**
     * {@inheritdoc}
     */
    public function countForeverUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int
    {
        return $this->findUsageByDates($earningRuleId, $subject);
    }

    /**
     * @param EarningRuleId  $earningRuleId
     * @param UsageSubject   $subject
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function findUsageByDates(EarningRuleId $earningRuleId, UsageSubject $subject, \DateTime $from = null, \DateTime $to = null): int
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('count(u)');
        $qb->andWhere('u.earningRule = :earningRule')->setParameter('earningRule', (string) $earningRuleId);
        $qb->andWhere('u.subject = :subject')->setParameter('subject', (string) $subject);
        if (null != $from && null != $to) {
            $qb->andWhere('u.date >= :start and u.date <= :end')
                ->setParameter('start', $from)
                ->setParameter('end', $to);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
