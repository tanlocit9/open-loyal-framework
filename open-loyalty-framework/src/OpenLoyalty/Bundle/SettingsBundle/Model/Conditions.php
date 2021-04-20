<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Model;

use OpenLoyalty\Component\Core\Infrastructure\FileInterface as BaseFileInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Conditions.
 */
class Conditions implements FileInterface
{
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
     * @var UploadedFile
     * @Assert\NotBlank()
     * @Assert\File(
     *     mimeTypes={"application/pdf"},
     *     maxSize="10M"
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
     * {@inheritdoc}
     */
    public function getResizedPath(string $path): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getSizes(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setSizes(array $sizes): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data = []): BaseFileInterface
    {
        $obj = new self();
        foreach ($data as $k => $v) {
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
            }
        }

        return $obj;
    }
}
