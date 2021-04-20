<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Seller\Infrastructure\Repository;

use OpenLoyalty\Component\Core\Infrastructure\Repository\OloyElasticsearchRepository;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetailsRepository;

/**
 * Class SellerDetailsElasticsearchRepository.
 */
class SellerDetailsElasticsearchRepository extends OloyElasticsearchRepository implements SellerDetailsRepository
{
    /**
     * {@inheritdoc}
     */
    public function findByParametersPaginated(
        array $params,
        $exact = true,
        $page = 1,
        $perPage = 10,
        $sortField = null,
        $direction = 'DESC'
    ): array {
        if ($page < 1) {
            $page = 1;
        }
        if ($perPage < 1) {
            $perPage = 10;
        }

        $filter = [];
        $filter[] = ['term' => ['deleted' => false]];

        foreach ($params as $key => $value) {
            if (!$exact) {
                $filter[] = ['wildcard' => [
                    $key => '*'.$value.'*',
                ]];
            } else {
                $filter[] = ['term' => [
                    $key => $value,
                ]];
            }
        }

        if ($sortField) {
            $sort = [
                $sortField => ['order' => strtolower($direction), 'ignore_unmapped' => true],
            ];
        } else {
            $sort = null;
        }

        if (count($filter) > 0) {
            $query = array(
                'bool' => array(
                    'must' => $filter,
                ),
            );
        } else {
            $query = array(
                'filtered' => array(
                    'query' => array(
                        'match_all' => array(),
                    ),
                ),
            );
        }

        return $this->paginatedQuery($query, ($page - 1) * $perPage, $perPage, $sort);
    }

    /**
     * {@inheritdoc}
     */
    public function countTotal(array $params = [], $exact = true): int
    {
        $filter = [];
        $filter[] = ['term' => ['deleted' => false]];

        foreach ($params as $key => $value) {
            if (!$exact) {
                $filter[] = ['wildcard' => [
                    $key => '*'.$value.'*',
                ]];
            } else {
                $filter[] = ['term' => [
                    $key => $value,
                ]];
            }
        }

        if (count($filter) > 0) {
            $query = array(
                'bool' => array(
                    'must' => $filter,
                ),
            );
        } else {
            $query = array(
                'filtered' => array(
                    'query' => array(
                        'match_all' => array(),
                    ),
                ),
            );
        }

        return $this->count($query);
    }
}
