<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine;

/**
 * Trait SortByFilter.
 */
trait SortByFilter
{
    /**
     * @var array
     */
    private $sortBy = ['ASC', 'DESC'];

    /**
     * Validate sort by direction.
     *
     * @param string $sortBy
     *
     * @return string
     */
    public function validateSortBy(string $sortBy)
    {
        if (!in_array(strtoupper($sortBy), $this->sortBy)) {
            return $this->sortBy[0];
        }

        return $sortBy;
    }
}
