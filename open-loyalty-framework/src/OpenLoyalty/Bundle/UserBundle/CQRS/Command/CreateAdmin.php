<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\CQRS\Command;

use Symfony\Component\Validator\Constraints as Assert;
use OpenLoyalty\Bundle\UserBundle\Validator\Constraint\PasswordRequirements;

/**
 * Class CreateAdmin.
 */
class CreateAdmin extends AdminCommand
{
    /**
     * @Assert\NotBlank(groups={"internal"})
     * @PasswordRequirements(
     *     requireSpecialCharacter=true,
     *     requireNumbers=true,
     *     requireLetters=true,
     *     requireCaseDiff=true,
     *     minLength="8"
     * )
     *
     * @var string
     */
    protected $plainPassword;

    /**
     * @var bool
     */
    protected $external;

    /**
     * @Assert\NotBlank(groups={"external"})
     *
     * @var string
     */
    protected $apiKey;

    /**
     * @var bool
     */
    protected $isActive;

    /**
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return bool|null
     */
    public function isExternal(): ?bool
    {
        return $this->external;
    }

    /**
     * @param bool $external
     */
    public function setExternal(bool $external): void
    {
        $this->external = $external;
    }

    /**
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return bool|null
     */
    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }
}
