<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\CQRS\Handler;

use Broadway\CommandHandling\SimpleCommandHandler;
use OpenLoyalty\Bundle\UserBundle\CQRS\Command\CreateRole;
use OpenLoyalty\Bundle\UserBundle\CQRS\Command\ChangeRole;
use OpenLoyalty\Bundle\UserBundle\CQRS\Command\DeleteRole;
use OpenLoyalty\Bundle\UserBundle\Entity\Permission;
use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use OpenLoyalty\Bundle\UserBundle\Service\AclManagerInterface;

/**
 * Class AdminHandler.
 */
class RoleHandler extends SimpleCommandHandler
{
    /**
     * @var AclManagerInterface
     */
    private $aclManager;

    /**
     * RoleHandler constructor.
     *
     * @param AclManagerInterface $aclManager
     */
    public function __construct(AclManagerInterface $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * @param CreateRole $command
     */
    public function handleCreateRole(CreateRole $command)
    {
        $role = new Role('ROLE_ADMIN', $command->getName(), false);
        foreach ($command->getPermissions() as $permission) {
            $role->addPermission(new Permission($permission['resource'], $permission['access']));
        }
        $this->aclManager->update($role);
    }

    /**
     * @param ChangeRole $command
     */
    public function handleChangeRole(ChangeRole $command)
    {
        $role = $this->aclManager->getRoleById($command->getId());
        $role->setName($command->getName());
        $role->getPermissions()->clear();
        foreach ($command->getPermissions() as $permission) {
            $role->addPermission(new Permission($permission['resource'], $permission['access']));
        }
        $this->aclManager->update($role);
    }

    /**
     * @param DeleteRole $command
     */
    public function handleDeleteRole(DeleteRole $command)
    {
        $this->aclManager->delete($command->getId());
    }
}
