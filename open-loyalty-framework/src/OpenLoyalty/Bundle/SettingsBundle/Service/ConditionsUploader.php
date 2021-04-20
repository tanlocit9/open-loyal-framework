<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Service;

use Gaufrette\Filesystem;
use OpenLoyalty\Bundle\SettingsBundle\Model\Conditions;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ConditionsUploader.
 */
class ConditionsUploader
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    const CONDITIONS = 'conditions';
    const CONDITIONS_FILENAME = 'terms-conditions';

    /**
     * @var string
     */
    private $customerPanelUrl;

    /**
     * FileUploader constructor.
     *
     * @param Filesystem  $filesystem
     * @param string|null $customerPanelUrl
     */
    public function __construct(Filesystem $filesystem, ?string $customerPanelUrl = null)
    {
        $this->filesystem = $filesystem;
        $this->customerPanelUrl = $customerPanelUrl;
    }

    /**
     * @param Conditions $conditions
     *
     * @return string|null
     */
    public function get(Conditions $conditions): ?string
    {
        if (null === $conditions || null === $conditions->getPath()) {
            return null;
        }

        return $this->filesystem->get($conditions->getPath())->getContent();
    }

    /**
     * @param UploadedFile $src
     *
     * @return Conditions
     */
    public function upload(UploadedFile $src): Conditions
    {
        $file = new Conditions();

        $file->setPath(self::CONDITIONS_FILENAME);
        $file->setMime($src->getClientMimeType());
        $file->setOriginalName($src->getClientOriginalName());

        $this->filesystem->write($file->getPath(), file_get_contents($src->getRealPath()), true);
        unlink($src->getRealPath());

        return $file;
    }

    /**
     * @param Conditions|null $conditions
     */
    public function remove(Conditions $conditions = null): void
    {
        if (null === $conditions || null === $conditions->getPath()) {
            return;
        }

        $path = $conditions->getPath();
        if ($this->filesystem->has($path)) {
            $this->filesystem->delete($path);
        }
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->customerPanelUrl.'#!/'.ConditionsUploader::CONDITIONS_FILENAME;
    }
}
