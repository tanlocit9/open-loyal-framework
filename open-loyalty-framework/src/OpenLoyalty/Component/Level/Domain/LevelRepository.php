<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Level\Domain;

/**
 * Interface LevelRepository.
 */
interface LevelRepository
{
    /**
     * @param LevelId $levelId
     *
     * @return null|object
     */
    public function byId(LevelId $levelId);

    /**
     * @return array
     */
    public function findAll();

    /**
     * @return array
     */
    public function findAllActive();

    /**
     * @param $percent
     *
     * @return null|object
     */
    public function findOneByRewardPercent($percent);

    /**
     * @param Level $level
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Level $level);

    /**
     * @param Level $level
     */
    public function remove(Level $level);

    /**
     * @param int    $page
     * @param int    $perPage
     * @param string $sortField
     * @param string $direction
     *
     * @return array
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = 'levelId', $direction = 'DESC');

    /**
     * @param int|null    $page
     * @param int|null    $perPage
     * @param null|string $sortField
     * @param null|string $direction
     *
     * @return mixed
     */
    public function findActivePaginated(?int $page = 1, ?int $perPage = 10, ?string $sortField = null, ?string $direction = 'ASC');

    /**
     * @return mixed
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countTotal();

    /**
     * @param $conditionValue
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findLevelByConditionValueWithTheBiggestReward($conditionValue);

    /**
     * @param $conditionValue
     * @param $currentLevelValue
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findNextLevelByConditionValueWithTheBiggestReward($conditionValue, $currentLevelValue);

    /**
     * @param $conditionValue
     * @param $currentLevelValue
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findPreviousLevelByConditionValueWithTheBiggestReward($conditionValue, $currentLevelValue);
}
