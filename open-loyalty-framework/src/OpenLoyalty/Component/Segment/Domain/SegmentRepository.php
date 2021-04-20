<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain;

/**
 * Interface SegmentRepository.
 */
interface SegmentRepository
{
    /**
     * @param SegmentId $segmentId
     *
     * @return null|object
     */
    public function byId(SegmentId $segmentId);

    /**
     * @param bool $returnQueryBuilder
     *
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function findAll($returnQueryBuilder = false);

    /**
     * @param bool $returnQueryBuilder
     *
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function findAllActive($returnQueryBuilder = false);

    /**
     * @param Segment $segment
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Segment $segment);

    /**
     * @param Segment $segment
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(Segment $segment);

    /**
     * @param int    $page
     * @param int    $perPage
     * @param string $sortField
     * @param string $direction
     * @param bool   $onlyActive
     *
     * @return array
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = 'segmentId', $direction = 'DESC', $onlyActive = false);

    /**
     * @return mixed
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countTotal();
}
