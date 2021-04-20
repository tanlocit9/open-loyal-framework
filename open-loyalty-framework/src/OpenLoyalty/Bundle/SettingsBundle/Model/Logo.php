<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Logo.
 */
class Logo implements FileInterface
{
    const SUB_PATH = 'logo';

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $originalName;

    /**
     * @var string
     */
    protected $mime;

    /**
     * @var array
     */
    protected $sizes = [];

    /**
     * @var UploadedFile
     * @Assert\NotBlank()
     * @Assert\File(
     *     mimeTypes={"image/png", "image/jpeg", "image/svg+xml", "text/html"},
     *     maxSize="2M"
     * )
     */
    protected $file;

    /**
     * {@inheritdoc}
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function setFile(UploadedFile $file): void
    {
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginalName(string $originalName): void
    {
        $this->originalName = $originalName;
    }

    /**
     * {@inheritdoc}
     */
    public function getMime(): ?string
    {
        return $this->mime;
    }

    /**
     * {@inheritdoc}
     */
    public function setMime(string $mime): void
    {
        $this->mime = $mime;
    }

    /**
     * @param array $sizes
     */
    public function setSizes(array $sizes): void
    {
        $this->sizes = $sizes;
    }

    /**
     * @return array
     */
    public function getSizes(): array
    {
        return $this->sizes;
    }

    /**
     * @param string $size
     *
     * @return string
     */
    public function getResizedPath(string $size): string
    {
        return str_replace(self::SUB_PATH, self::SUB_PATH.DIRECTORY_SEPARATOR.$size, $this->getPath());
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data = []): \OpenLoyalty\Component\Core\Infrastructure\FileInterface
    {
        $obj = new self();
        foreach ($data as $k => $v) {
            if (empty($v)) {
                continue;
            }
            switch ($k) {
                case 'originalName':
                    $obj->setOriginalName($v);
                    break;
                case 'path':
                    $obj->setPath($v);
                    break;
                case 'mime':
                    $obj->setMime($v);
                    break;
                case 'sizes':
                    $obj->setSizes($v);
                    break;
            }
        }

        return $obj;
    }
}
