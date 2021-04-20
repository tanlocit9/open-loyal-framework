<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Service;

use OpenLoyalty\Bundle\UserBundle\Entity\Role;

/**
 * Interface AclManagerInterface.
 */
interface AclManagerInterface
{
    /**
     * @return array
     */
    public function getAdminRoles(): array;

    /**
     * @return Role
     */
    public function getAdminMasterRole(): Role;

    /**
     * @param int $id
     *
     * @return null|Role
     */
    public function getRoleById(int $id): ?Role;

    /**
     * @return array
     */
    public function getAvailableAccesses(): array;

    /**
     * @return array
     */
    public function getAvailableResources(): array;

    /**
     * @param Role $role
     */
    public function update(Role $role): void;

    /**
     * @param int $roleId
     */
    public function delete(int $roleId): void;
}
