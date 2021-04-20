<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Service;

use Gumlet\ImageResize;
use OpenLoyalty\Component\Core\Infrastructure\FileInterface;
use OpenLoyalty\Component\Core\Infrastructure\ImageResizerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ImageResizer.
 */
class ImageResizer implements ImageResizerInterface
{
    const LOGO_SUBDIR = 'logo';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $map;

    /**
     * @var string
     */
    private $directory;

    /**
     * ImageResizer constructor.
     *
     * @param Filesystem $filesystem
     * @param string     $directory
     * @param array      $config
     *
     * @throws \Exception
     */
    public function __construct(Filesystem $filesystem, string $directory, array $config = [])
    {
        $this->filesystem = $filesystem;
        $this->map = $config;

        if (!$filesystem->exists($directory)) {
            throw new \Exception(sprintf('Provided directory "%s" is not readable.', $directory));
        }

        $this->directory = $directory;
    }

    /**
     * @param FileInterface $file
     * @param string        $type
     *
     * @return array
     *
     * @throws \Exception
     */
    public function resize(FileInterface $file, string $type): array
    {
        $resizedFiles = [];
        $type = str_replace('-', '_', $type);

        if (!isset($this->map[$type])) {
            return [];
        }

        $filepath = sprintf('%s/%s', $this->directory, $file->getPath());

        if (!$this->filesystem->exists($filepath)) {
            throw new \Exception(sprintf('%s file does not exist.', $file->getOriginalName()));
        }

        $sizes = $this->getMappedSizes($type);

        foreach ($sizes as $size) {
            $targetDir = $this->getTargetDir($size);
            if (!$this->filesystem->exists($targetDir)) {
                $this->filesystem->mkdir($targetDir);
            }

            $width = $size['width'];
            $height = $size['height'];

            $newFile = $this->createResizeInstance($filepath);
            $newFile->resize($width, $height);
            $targetFile = sprintf('%s%s', $targetDir, basename($file->getPath()));
            $resizedFiles[sprintf('%sx%s', $width, $height)] = $newFile->save($targetFile);
        }

        return $resizedFiles;
    }

    /**
     * @param array $map
     */
    public function setMap(array $map)
    {
        $this->map = $map;
    }

    /**
     * @param string $filepath
     *
     * @return ImageResize
     */
    public function createResizeInstance(string $filepath): ImageResize
    {
        return new ImageResize($filepath);
    }

    /**
     * @param array $dimensions
     *
     * @return string
     */
    private function getTargetDir(array $dimensions): string
    {
        return sprintf(
            '%s/%s/%sx%s/',
            $this->directory,
            self::LOGO_SUBDIR,
            $dimensions['width'],
            $dimensions['height']
        );
    }

    /**
     * @param string $type
     *
     * @return array
     */
    private function getMappedSizes(string $type): array
    {
        return $this->map[$type]['sizes'] ?? [];
    }
}
