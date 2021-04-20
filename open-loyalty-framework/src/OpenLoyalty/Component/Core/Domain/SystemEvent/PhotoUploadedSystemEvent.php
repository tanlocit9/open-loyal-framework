<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Domain\SystemEvent;

use OpenLoyalty\Component\Core\Infrastructure\FileInterface;

/**
 * Class PhotoUploadedSystemEvent.
 */
class PhotoUploadedSystemEvent
{
    /**
     * @var FileInterface
     */
    private $originFile;

    /**
     * @var string
     */
    private $name;

    /**
     * PhotoUploadedSystemEvent constructor.
     *
     * @param FileInterface $originFile
     * @param string        $name
     */
    public function __construct(FileInterface $originFile, string $name)
    {
        $this->originFile = $originFile;
        $this->name = $name;
    }

    /**
     * @return FileInterface
     */
    public function getOriginFile(): FileInterface
    {
        return $this->originFile;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
