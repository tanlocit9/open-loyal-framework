<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\ReadModel;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Exception\TooManyResultsException;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;

interface CustomerDetailsRepository extends Repository
{
    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @param bool      $onlyActive
     *
     * @return CustomerDetails[]
     */
    public function findByBirthdayAnniversary(\DateTime $from, \DateTime $to, $onlyActive = true): array;

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @param bool      $onlyActive
     *
     * @return CustomerDetails[]
     */
    public function findByCreationAnniversary(\DateTime $from, \DateTime $to, $onlyActive = true): array;

    /**
     * @param array $params
     * @param bool  $exact
     *
     * @return CustomerDetails[]
     */
    public function findByParameters(array $params, $exact = true): array;

    /**
     * @param \DateTime $currentDate
     * @param int       $recalculationIntervalInDays
     *
     * @return CustomerDetails[]
     */
    public function findAllForLevelRecalculation(\DateTime $currentDate, int $recalculationIntervalInDays): array;

    /**
     * @param array  $params
     * @param bool   $exact
     * @param int    $page
     * @param int    $perPage
     * @param null   $sortField
     * @param string $direction
     *
     * @return CustomerDetails[]
     */
    public function findByParametersPaginated(array $params, $exact = true, $page = 1, $perPage = 10, $sortField = null, $direction = 'DESC'): array;

    /**
     * @param array $params
     * @param bool  $exact
     *
     * @return int
     */
    public function countTotal(array $params = [], $exact = true): int;

    /**
     * @param CustomerId  $customerId
     * @param int         $page
     * @param int         $perPage
     * @param null        $sortField
     * @param string      $direction
     * @param bool        $showCashback
     * @param string|null $deliveryStatus
     *
     * @return CampaignPurchase[]
     */
    public function findPurchasesByCustomerIdPaginated(
        CustomerId $customerId,
        $page = 1,
        $perPage = 10,
        $sortField = null,
        $direction = 'DESC',
        $showCashback = false,
        ?string $deliveryStatus = null
    ): array;

    /**
     * @return CustomerDetails[]
     */
    public function findCustomersWithPurchasesToActivate(): array;

    /**
     * @return CustomerDetails[]
     */
    public function findCustomersWithPurchasesToExpire(): array;

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return CustomerDetails[]
     */
    public function findCustomersWithPurchasesExpiringAt(\DateTimeInterface $dateTime): array;

    /**
     * @param CustomerId $customerId
     * @param bool       $showCashback
     *
     * @return int
     */
    public function countPurchasesByCustomerId(CustomerId $customerId, $showCashback = false): int;

    /**
     * @param $criteria
     * @param $limit
     *
     * @return CustomerDetails[]
     */
    public function findOneByCriteria($criteria, $limit): array;

    /**
     * @param $criteria
     *
     * @return CustomerDetails[]
     */
    public function findByAnyCriteria($criteria): array;

    /**
     * @param array $fields
     * @param int   $limit
     *
     * @return CustomerDetails[]
     *
     * @throws TooManyResultsException
     */
    public function findCustomersByParameters(array $fields, int $limit): array;

    /**
     * @param $from
     * @param $to
     * @param bool $onlyActive
     *
     * @return CustomerDetails[]
     */
    public function findAllWithAverageTransactionAmountBetween($from, $to, $onlyActive = true): array;

    /**
     * @param $from
     * @param $to
     * @param bool $onlyActive
     *
     * @return CustomerDetails[]
     */
    public function findAllWithTransactionAmountBetween($from, $to, $onlyActive = true): array;

    /**
     * @param $from
     * @param $to
     * @param bool $onlyActive
     *
     * @return CustomerDetails[]
     */
    public function findAllWithTransactionCountBetween($from, $to, $onlyActive = true): array;

    /**
     * @param $fieldName
     *
     * @return float
     */
    public function sumAllByField($fieldName): float;

    /**
     * @param array $labels
     * @param null  $active
     *
     * @return CustomerDetails[]
     */
    public function findByLabels(array $labels, $active = null): array;

    /**
     * @param array $labels
     * @param null  $active
     *
     * @return CustomerDetails[]
     */
    public function findWithLabels(array $labels, $active = null): array;

    /**
     * @param string[] $customerIds
     *
     * @return CustomerDetails[]
     */
    public function findByIds(array $customerIds): array;

    /**
     * @param string      $phoneNumber
     * @param string|null $customerId
     *
     * @return array
     */
    public function findOneByPhone(string $phoneNumber, ?string $customerId = null): array;
}
