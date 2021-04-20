<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\LevelBundle\Service;

use Gaufrette\Filesystem;
use OpenLoyalty\Component\Level\Domain\Model\LevelPhoto;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class LevelPhotoUploader.
 */
class LevelPhotoUploader
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * FileUploader constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param LevelPhoto $photo
     *
     * @return string
     */
    public function get(LevelPhoto $photo)
    {
        if (null === $photo || null === $photo->getPath()) {
            return '';
        }

        return $this->filesystem->get($photo->getPath())->getContent();
    }

    /**
     * @param UploadedFile $src
     *
     * @return LevelPhoto
     */
    public function upload(UploadedFile $src)
    {
        $file = new LevelPhoto();
        $fileName = md5(uniqid()).'.'.$src->guessExtension();
        $file->setPath('level_photos'.DIRECTORY_SEPARATOR.$fileName);
        $file->setMime($src->getClientMimeType());
        $file->setOriginalName($src->getClientOriginalName());

        $this->filesystem->write($file->getPath(), file_get_contents($src->getRealPath()));
        unlink($src->getRealPath());

        return $file;
    }

    /**
     * @param LevelPhoto|null $file
     */
    public function remove(LevelPhoto $file = null)
    {
        if (null === $file || null === $file->getPath()) {
            return;
        }

        $path = $file->getPath();
        if ($this->filesystem->has($path)) {
            $this->filesystem->delete($path);
        }
    }
}
