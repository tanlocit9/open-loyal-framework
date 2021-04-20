<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Service;

use Gaufrette\Filesystem;
use OpenLoyalty\Component\EarningRule\Domain\Model\EarningRulePhoto;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class EarningRulePhotoUploader.
 */
class EarningRulePhotoUploader
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
     * @param EarningRulePhoto $photo
     *
     * @return string
     */
    public function get(EarningRulePhoto $photo)
    {
        if (null === $photo || null === $photo->getPath()) {
            return '';
        }

        return $this->filesystem->get($photo->getPath())->getContent();
    }

    /**
     * @param UploadedFile $src
     *
     * @return EarningRulePhoto
     */
    public function upload(UploadedFile $src): EarningRulePhoto
    {
        $file = new EarningRulePhoto();
        $fileName = md5(uniqid()).'.'.$src->guessExtension();
        $file->setPath('earning_rule_photos'.DIRECTORY_SEPARATOR.$fileName);
        $file->setMime($src->getClientMimeType());
        $file->setOriginalName($src->getClientOriginalName());

        $this->filesystem->write($file->getPath(), file_get_contents($src->getRealPath()));
        unlink($src->getRealPath());

        return $file;
    }

    /**
     * @param EarningRulePhoto|null $file
     */
    public function remove(EarningRulePhoto $file = null)
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
