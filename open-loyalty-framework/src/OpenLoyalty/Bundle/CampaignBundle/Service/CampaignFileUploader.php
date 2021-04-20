<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Service;

use Gaufrette\Filesystem;
use OpenLoyalty\Component\Campaign\Domain\Model\CampaignFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class CampaignFileUploader.
 */
abstract class CampaignFileUploader
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @return string
     */
    abstract public function getFolderName(): string;

    /**
     * @return CampaignFile
     */
    abstract public function getNewInstance(): CampaignFile;

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
     * @param CampaignFile $file
     *
     * @return string|null
     */
    public function get(CampaignFile $file): ?string
    {
        if (null === $file || null === $file->getPath()) {
            return null;
        }

        return $this->filesystem->get($file->getPath())->getContent();
    }

    /**
     * @param UploadedFile $src
     *
     * @return CampaignFile
     */
    public function upload(UploadedFile $src): CampaignFile
    {
        $file = $this->getNewInstance();
        $fileName = md5(uniqid()).'.'.$src->guessExtension();
        $file->setPath($this->getFolderName().DIRECTORY_SEPARATOR.$fileName);
        $file->setMime($src->getClientMimeType());
        $file->setOriginalName($src->getClientOriginalName());

        $this->filesystem->write($file->getPath(), file_get_contents($src->getRealPath()));
        unlink($src->getRealPath());

        return $file;
    }

    /**
     * @param CampaignFile $file
     */
    public function remove(CampaignFile $file = null): void
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
