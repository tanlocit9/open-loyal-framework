<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;
use OpenLoyalty\Bundle\SettingsBundle\Validator\Constraints as AppAssert;

/**
 * Class TranslationsEntry.
 */
class TranslationsEntry
{
    /**
     * @var string
     */
    private $code = null;

    /**
     * @var string
     */
    private $name = null;

    /**
     * @var bool
     */
    private $default = false;

    /**
     * @var int
     */
    private $order = 0;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @AppAssert\ValidJson()
     */
    private $content = null;

    /**
     * @var \DateTime
     */
    private $updatedAt = null;

    /**
     * TranslationsEntry constructor.
     *
     * @param string|null $code
     * @param string|null $name
     * @param string|null $content
     * @param \DateTime   $updatedAt
     * @param int         $order
     * @param bool        $default
     */
    public function __construct(string $code = null, string $name = null, string $content = null,
                                \DateTime $updatedAt = null, int $order = 0, bool $default = false)
    {
        $this->code = $code;
        $this->name = $name;
        $this->content = $content;
        $this->updatedAt = $updatedAt;
        $this->order = $order;
        $this->default = $default;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;
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
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * @param bool $default
     */
    public function setDefault(bool $default)
    {
        $this->default = $default;
    }

    /**
     * @return int|null
     */
    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(?int $order)
    {
        $this->order = $order;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content)
    {
        $this->content = $content;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}
