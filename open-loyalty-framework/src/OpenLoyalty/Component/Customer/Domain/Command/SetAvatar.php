<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Domain\Command;

use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class SetAvatar.
 */
class SetAvatar extends CustomerCommand
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $originalName;

    /**
     * @var string
     */
    private $mime;

    /**
     * SetAvatar constructor.
     *
     * @param CustomerId $customerId
     * @param string     $path
     * @param string     $originalName
     * @param string     $mime
     */
    public function __construct(CustomerId $customerId, string $path, string $originalName, string $mime)
    {
        parent::__construct($customerId);
        $this->path = $path;
        $this->originalName = $originalName;
        $this->mime = $mime;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    /**
     * @param string $originalName
     */
    public function setOriginalName(string $originalName): void
    {
        $this->originalName = $originalName;
    }

    /**
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * @param string $mime
     */
    public function setMime(string $mime): void
    {
        $this->mime = $mime;
    }
}
