<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\CQRS\Command;

use OpenLoyalty\Bundle\UserBundle\CQRS\AdminId;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AdminCommand.
 */
class AdminCommand
{
    /**
     * @var AdminId
     */
    protected $adminId;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $email;

    /**
     * @var null|array
     */
    protected $roles;

    /**
     * AdminCommand constructor.
     *
     * @param AdminId $adminId
     */
    public function __construct(AdminId $adminId)
    {
        $this->adminId = $adminId;
    }

    /**
     * @return AdminId
     */
    public function getAdminId(): AdminId
    {
        return $this->adminId;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return null|array
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}
