<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use Symfony\Component\Security\Core\Role\RoleInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="OpenLoyalty\Bundle\UserBundle\Entity\Repository\RoleRepository")
 * @ORM\Table(name="ol__roles")
 * @JMS\ExclusionPolicy("all")
 */
class Role implements RoleInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", name="name", nullable=true, length=50)
     * @JMS\Expose()
     */
    private $name;

    /**
     * @ORM\Column(type="string", name="role", length=50)
     * @JMS\Expose()
     */
    private $role;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="is_master", options={"default": false})
     * @JMS\Expose()
     */
    private $master;

    /**
     * @var Permission[]
     * @ORM\OneToMany(targetEntity="OpenLoyalty\Bundle\UserBundle\Entity\Permission", orphanRemoval=true,
     *     cascade={"persist", "remove"}, mappedBy="role")*
     * @JMS\Expose()
     */
    private $permissions;

    /**
     * Role constructor.
     *
     * @param string      $role
     * @param string|null $name
     * @param bool        $isMaster
     */
    public function __construct(string $role, string $name = null, bool $isMaster = false)
    {
        $this->role = $role;
        $this->master = $isMaster;
        $this->permissions = new ArrayCollection();
        $this->name = $name ?? $role;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->role;
    }

    /**
     * @return Collection
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    /**
     * @param Permission[] $permissions
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = new ArrayCollection($permissions);
    }

    /**
     * @param Permission $permission
     */
    public function addPermission(Permission $permission): void
    {
        $permission->setRole($this);
        $this->permissions->add($permission);
    }

    /**
     * @return array
     */
    public function getRealPermissions(): array
    {
        $permissions = [];

        /** @var Permission $permission */
        foreach ($this->getPermissions() as $permission) {
            $permissions[] = $permission;

            if ($permission->getAccess() == PermissionAccess::MODIFY) {
                $permissions[] = new Permission($permission->getResource(), PermissionAccess::VIEW);
            }
        }

        return $permissions;
    }

    /**
     * @param string $resource
     * @param string $access
     *
     * @return bool
     */
    public function hasPermission(string $resource, string $access): bool
    {
        /** @var Permission $permission */
        foreach ($this->getRealPermissions() as $permission) {
            if ($resource === $permission->getResource() && $access === $permission->getAccess()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isMaster(): bool
    {
        return $this->master;
    }

    /**
     * @param bool $master
     */
    public function setMaster(bool $master): void
    {
        $this->master = $master;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
