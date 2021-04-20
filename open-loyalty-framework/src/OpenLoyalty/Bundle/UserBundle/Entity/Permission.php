<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ol__permission")
 */
class Permission
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", name="resource", length=64)
     */
    private $resource;

    /**
     * @var string
     * @ORM\Column(type="string", name="access")
     */
    private $access;

    /**
     * @var Role
     * @ORM\ManyToOne(targetEntity="OpenLoyalty\Bundle\UserBundle\Entity\Role", inversedBy="permissions")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $role;

    /**
     * Permission constructor.
     *
     * @param string $resource
     * @param string $access
     */
    public function __construct(string $resource, string $access)
    {
        $this->resource = $resource;
        $this->access = $access;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @param string $resource
     */
    public function setResource(string $resource): void
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getAccess(): string
    {
        return $this->access;
    }

    /**
     * @param string $access
     */
    public function setAccess(string $access): void
    {
        $this->access = $access;
    }

    /**
     * @return Role
     */
    public function getRole(): Role
    {
        return $this->role;
    }

    /**
     * @param Role $role
     */
    public function setRole(Role $role): void
    {
        $this->role = $role;
    }
}
