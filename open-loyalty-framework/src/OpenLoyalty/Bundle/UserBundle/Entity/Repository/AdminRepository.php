<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Entity\Repository;

use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Bundle\UserBundle\Exception\AdminNotFoundException;

/**
 * Interface AdminRepository.
 */
interface AdminRepository
{
    /**
     * @param int    $page
     * @param int    $perPage
     * @param null   $sortField
     * @param string $direction
     *
     * @return Admin[]
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = null, $direction = 'ASC');

    /**
     * @return int
     */
    public function countTotal();

    /**
     * @param $email
     * @param $excludedId
     *
     * @return bool
     */
    public function isEmailExist($email, $excludedId = null);

    /**
     * @param string $adminId
     *
     * @throws AdminNotFoundException
     *
     * @return Admin
     */
    public function findById(string $adminId): Admin;
}
