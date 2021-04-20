<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Domain\Model;

/**
 * Class Avatar.
 */
class Avatar
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
     * Avatar constructor.
     *
     * @param string $path
     * @param string $originalName
     * @param string $mime
     */
    public function __construct(string $path, string $originalName, string $mime)
    {
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
     * @return string
     */
    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    /**
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }
}
