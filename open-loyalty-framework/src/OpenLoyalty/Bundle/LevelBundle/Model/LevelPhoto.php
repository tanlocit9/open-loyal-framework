<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\LevelBundle\Model;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use OpenLoyalty\Bundle\LevelBundle\Validator\Constraints as LevelAssert;
use OpenLoyalty\Component\Level\Domain\Model\LevelPhoto as DomainLevelPhoto;

/**
 * Class LevelPhoto.
 */
class LevelPhoto extends DomainLevelPhoto
{
    /**
     * @var UploadedFile
     * @Assert\NotBlank()
     * @LevelAssert\Image(
     *     mimeTypes={"image/png", "image/gif", "image/jpeg"},
     *     maxSize="2M"
     * )
     */
    protected $file;

    /**
     * @return UploadedFile| null
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param File $file
     *
     * @return LevelPhoto
     */
    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }
}
