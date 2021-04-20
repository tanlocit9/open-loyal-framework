<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Domain\Command;

use OpenLoyalty\Component\Core\Infrastructure\FileInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class LogoCommand.
 */
abstract class LogoCommand
{
    /**
     * @var File
     */
    protected $logo;
    /**
     * @var string
     */
    protected $type;

    /**
     * LogoCommand constructor.
     *
     * @param FileInterface $logo
     * @param string        $imageType
     */
    public function __construct(FileInterface $logo, string $imageType)
    {
        $this->logo = $logo;
        $this->type = $imageType;
    }

    /**
     * @return FileInterface
     */
    public function getLogo(): FileInterface
    {
        return $this->logo;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
