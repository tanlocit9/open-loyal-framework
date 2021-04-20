<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use OpenLoyalty\Component\Core\Domain\Model\Identifier;

/**
 * Interface EarningRuleRepository.
 */
interface EarningRuleRepository
{
    /**
     * @param EarningRuleId $earningRuleId
     *
     * @return null|object
     */
    public function byId(EarningRuleId $earningRuleId);

    /**
     * @param bool $returnQueryBuilder
     *
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function findAll($returnQueryBuilder = false);

    /**
     * @param                        $eventName
     * @param array                  $segmentIds
     * @param null                   $levelId
     * @param \DateTime|null         $date
     * @param string|null|Identifier $posId
     *
     * @return array
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function findByCustomEventName(
        $eventName,
        array $segmentIds = [],
        $levelId = null,
        \DateTime $date = null,
        $posId = null
    );

    /**
     * @param                        $eventName
     * @param array                  $segmentIds
     * @param null                   $levelId
     * @param \DateTime|null         $date
     * @param string|null|Identifier $posId
     *
     * @return array
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function findReferralByEventName(
        $eventName,
        array $segmentIds = [],
        $levelId = null,
        \DateTime $date = null,
        $posId = null
    );

    /**
     * @param \DateTime|null $date
     *
     * @return array
     */
    public function findAllActive(\DateTime $date = null);

    /**
     * @param null                   $eventName
     * @param array                  $segmentIds
     * @param null                   $levelId
     * @param \DateTime|null         $date
     * @param string|null|Identifier $posId
     *
     * @return array
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function findAllActiveEventRules(
        $eventName = null,
        array $segmentIds = [],
        $levelId = null,
        \DateTime $date = null,
        $posId = null
    );

    /**
     * @param int    $page
     * @param int    $perPage
     * @param null   $sortField
     * @param string $direction
     *
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = null, $direction = 'DESC');

    /**
     * @param array  $params
     * @param int    $page
     * @param int    $perPage
     * @param null   $sortField
     * @param string $direction
     *
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function findByParametersPaginated(
        array $params,
        $page = 1,
        $perPage = 10,
        $sortField = null,
        $direction = 'DESC'
    );

    /**
     * @param array  $params
     * @param null   $sortField
     * @param string $direction
     *
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function findByParameters(
        array $params,
        $sortField = null,
        $direction = 'DESC'
    );

    /**
     * @return \Doctrine\ORM\QueryBuilder|mixed
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countTotal();

    /**
     * @param array $params
     *
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function countFindByParameters(array $params);

    /**
     * @param EarningRule $earningRule
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(EarningRule $earningRule);

    /**
     * @param EarningRule $earningRule
     */
    public function remove(EarningRule $earningRule);

    /**
     * @param      $eventName
     * @param null $currentEarningRuleId
     *
     * @return bool
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isCustomEventEarningRuleExist($eventName, $currentEarningRuleId = null);

    /**
     * Find all active event rules filtered by level and segments.
     *
     * @param \DateTime|null $date
     * @param array          $segmentIds
     * @param LevelId|null   $levelId
     * @param PosId|null     $posId
     *
     * @return array
     */
    public function findAllActiveEventRulesBySegmentsAndLevels(
        \DateTime $date = null,
        array $segmentIds = [],
        $levelId = null,
        $posId = null
    );
}
