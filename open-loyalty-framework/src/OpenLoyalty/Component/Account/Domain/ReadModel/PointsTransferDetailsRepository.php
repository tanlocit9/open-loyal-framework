<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\ReadModel;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Bundle\PaginationBundle\Model\Pagination;

interface PointsTransferDetailsRepository extends Repository
{
    /**
     * @param \DateTime $dateTime
     *
     * @return array
     */
    public function findAllActiveAddingTransfersExpiredAfter(\DateTime $dateTime): array;

    /**
     * @param \DateTime $dateTime
     *
     * @return array
     */
    public function findAllActiveAddingTransfersExpiredAt(\DateTimeInterface $dateTime): array;

    /**
     * @param \DateTime $dateTime
     *
     * @return PointsTransferDetails[]
     */
    public function findAllPendingAddingTransfersToUnlock(\DateTime $dateTime): array;

    /**
     * @param \DateTime $dateTime
     *
     * @return PointsTransferDetails[]
     */
    public function findAllActiveAddingTransfersCreatedAfter(\DateTime $dateTime): array;

    /**
     * @param int    $page
     * @param int    $perPage
     * @param string $sortField
     * @param string $direction
     *
     * @return mixed
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = 'earningRuleId', $direction = 'DESC');

    /**
     * @param array  $params
     * @param bool   $exact
     * @param int    $page
     * @param int    $perPage
     * @param null   $sortField
     * @param string $direction
     *
     * @return mixed
     */
    public function findByParametersPaginated(
        array $params,
        $exact = true,
        $page = 1,
        $perPage = 10,
        $sortField = null,
        $direction = 'DESC'
    );

    /**
     * @param array      $parameters
     * @param Pagination $pagination
     *
     * @return array
     */
    public function findByParametersPaginatedAndFiltered(array $parameters, Pagination $pagination): array;

    /**
     * @param array $params
     * @param bool  $exact
     *
     * @return int
     */
    public function countTotal(array $params = [], $exact = true): int;

    /**
     * @return int
     */
    public function countTotalSpendingTransfers(): int;

    /**
     * @return int
     */
    public function getTotalValueOfSpendingTransfers(): int;
}
