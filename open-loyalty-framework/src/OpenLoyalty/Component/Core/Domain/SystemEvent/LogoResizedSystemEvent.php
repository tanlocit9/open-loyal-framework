<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Domain\SystemEvent;

use OpenLoyalty\Component\Core\Infrastructure\FileInterface;

/**
 * Class LogoResizedSystemEvent.
 */
class LogoResizedSystemEvent
{
    /**
     * @var
     */
    protected $originFile;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $resizedImages = [];

    /**
     * LogoResizedSystemEvent constructor.
     *
     * @param FileInterface $originFile
     * @param string        $type
     * @param array         $resizedImages
     */
    public function __construct(FileInterface $originFile, string $type, $resizedImages = [])
    {
        $this->originFile = $originFile;
        $this->type = $type;
        $this->resizedImages = $resizedImages;
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getResizedImages(): array
    {
        return $this->resizedImages;
    }
}
