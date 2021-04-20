<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Domain\Command;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class UploadPhoto.
 */
class UploadPhoto extends PhotoCommand
{
    /**
     * @var UploadedFile
     */
    private $file;

    /**
     * UploadPhoto constructor.
     *
     * @param string       $name
     * @param UploadedFile $file
     */
    public function __construct(string $name, UploadedFile $file)
    {
        parent::__construct($name);
        $this->file = $file;
    }

    /**
     * @return UploadedFile
     */
    public function getFile(): UploadedFile
    {
        return $this->file;
    }
}
