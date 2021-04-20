<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Service;

use Gaufrette\Filesystem;
use OpenLoyalty\Component\Customer\Domain\Model\Avatar;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class AvatarUploader.
 */
class AvatarUploader
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
     * @param string|null $path
     *
     * @return string
     */
    public function get(string $path = null): string
    {
        if (null === $path) {
            return '';
        }

        return $this->filesystem->get($path)->getContent();
    }

    /**
     * @param UploadedFile $src
     *
     * @return Avatar
     */
    public function upload(UploadedFile $src): Avatar
    {
        $fileName = md5(uniqid()).'.'.$src->guessExtension();
        $file = new Avatar(
            'avatars'.DIRECTORY_SEPARATOR.$fileName,
            $src->getClientOriginalName(),
            $src->getClientMimeType()
        );
        $this->filesystem->write($file->getPath(), file_get_contents($src->getRealPath()));
        unlink($src->getRealPath());

        return $file;
    }

    /**
     * @param string|null $path
     */
    public function remove(string $path = null): void
    {
        if (null === $path) {
            return;
        }

        if ($this->filesystem->has($path)) {
            $this->filesystem->delete($path);
        }
    }
}
