<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ImportBundle\Service;

use Gaufrette\Filesystem;
use OpenLoyalty\Bundle\ImportBundle\Model\ImportFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ImportFileManager.
 */
class ImportFileManager
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $directory;

    /**
     * ImportFileManager constructor.
     *
     * @param Filesystem $filesystem
     * @param string     $directory
     */
    public function __construct(Filesystem $filesystem, string $directory)
    {
        $this->filesystem = $filesystem;
        $this->directory = $directory;
    }

    /**
     * @return ImportFile
     */
    protected function createImportFile(): ImportFile
    {
        return new ImportFile();
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param string       $prefixName
     *
     * @return ImportFile
     *
     * @throws \Exception
     */
    public function upload(UploadedFile $uploadedFile, string $prefixName = 'import')
    {
        $file = $this->createImportFile();
        $fileName = $this->getFileName($uploadedFile, $prefixName);
        $file->setPath($fileName);
        $file->setMime($uploadedFile->getClientMimeType());
        $file->setOriginalName($uploadedFile->getClientOriginalName());

        if (!file_exists($uploadedFile->getRealPath())) {
            throw new \Exception("Uploaded file doesn't exist");
        }

        $this->filesystem->write($file->getPath(), file_get_contents($uploadedFile->getRealPath()));
        unlink($uploadedFile->getRealPath());

        return $file;
    }

    /**
     * @param ImportFile $importFile
     *
     * @return string
     */
    public function getAbsolutePath(ImportFile $importFile): string
    {
        return sprintf('%s/%s', $this->directory, $importFile->getPath());
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param string       $prefixName
     *
     * @return string
     */
    protected function getFileName(UploadedFile $uploadedFile, string $prefixName = ''): string
    {
        return sprintf(
            '%s-%s-%s.%s',
            $prefixName,
            date('Y-m-d_Hi'),
            uniqid(),
            $uploadedFile->guessExtension()
        );
    }
}
