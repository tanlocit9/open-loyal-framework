<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\ReadModel;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Customer\Domain\LevelId;

interface CustomersBelongingToOneLevelRepository extends Repository
{
    public function findByLevelIdPaginated(LevelId $levelId, $page = 1, $perPage = 10, $sortField = null, $direction = 'DESC');

    public function countByLevelId(LevelId $levelId);
}
