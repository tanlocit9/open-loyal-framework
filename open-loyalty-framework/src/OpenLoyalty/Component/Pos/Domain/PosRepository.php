<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Pos\Domain;

interface PosRepository
{
    /**
     * @param PosId $posId
     *
     * @return null|object
     */
    public function byId(PosId $posId);

    /**
     * @param $identifier
     *
     * @return null|object
     */
    public function oneByIdentifier($identifier);

    /**
     * @param bool $returnQueryBuilder
     *
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function findAll($returnQueryBuilder = false);

    /**
     * @param Pos $pos
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Pos $pos);

    /**
     * @param Pos $pos
     */
    public function remove(Pos $pos);

    /**
     * @param int    $page
     * @param int    $perPage
     * @param null   $sortField
     * @param string $direction
     *
     * @return array
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = null, $direction = 'DESC');

    /**
     * @return mixed
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countTotal();

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array the objects
     *
     * @throws \UnexpectedValueException
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
}
