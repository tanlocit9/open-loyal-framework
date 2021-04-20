<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Entity;

/**
 * Interface PermissionStorageInterface.
 */
interface PermissionStorageInterface
{
    /**
     * @return Permission[]
     */
    public function getPermissions(): array;
}
