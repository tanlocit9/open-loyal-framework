<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\ReadModel;

use Broadway\ReadModel\Repository;

interface TransactionDetailsRepository extends Repository
{
    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @param bool      $onlyWithCustomers
     *
     * @return TransactionDetails[]
     */
    public function findInPeriod(\DateTime $from, \DateTime $to, $onlyWithCustomers = true): array;

    /**
     * @return TransactionDetails[]
     */
    public function findAllWithCustomer(): array;

    /**
     * @param array $skuIds
     * @param bool  $withCustomer
     *
     * @return TransactionDetails[]
     */
    public function findBySKUs(array $skuIds, $withCustomer = true): array;

    /**
     * @param array $makers
     * @param bool  $withCustomer
     *
     * @return TransactionDetails[]
     */
    public function findByMakers(array $makers, $withCustomer = true): array;

    /**
     * @param array $labels
     * @param bool  $withCustomer
     *
     * @return TransactionDetails[]
     */
    public function findByLabels(array $labels, $withCustomer = true): array;

    /**
     * @return array
     */
    public function getAvailableLabels(): array;

    /**
     * @param array $params
     * @param bool  $exact
     *
     * @return TransactionDetails[]
     */
    public function findByParameters(array $params, $exact = true): array;

    /**
     * @param array  $params
     * @param bool   $exact
     * @param int    $page
     * @param int    $perPage
     * @param null   $sortField
     * @param string $direction
     *
     * @return TransactionDetails[]
     */
    public function findByParametersPaginated(array $params, $exact = true, $page = 1, $perPage = 10, $sortField = null, $direction = 'DESC'): array;

    /**
     * @param string $documentNumber
     * @param bool   $customer
     *
     * @return TransactionDetails|null
     */
    public function findTransactionByDocumentNumber(string $documentNumber, bool $customer = false): ?TransactionDetails;

    /**
     * @param string $documentNumber
     * @param bool   $customer
     *
     * @return TransactionDetails[]
     */
    public function findReturnsByDocumentNumber(string $documentNumber, bool $customer = true): array;

    /**
     * @param array $params
     * @param bool  $exact
     *
     * @return int
     */
    public function countTotal(array $params = [], $exact = true): int;
}
