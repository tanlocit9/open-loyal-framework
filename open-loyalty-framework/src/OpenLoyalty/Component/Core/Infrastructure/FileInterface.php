<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Infrastructure;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface FileInterface.
 */
interface FileInterface
{
    /**
     * @return null|UploadedFile
     */
    public function getFile(): ?UploadedFile;

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file): void;

    /**
     * @return null|string
     */
    public function getPath(): ?string;

    /**
     * @param string $path
     */
    public function setPath(string $path): void;

    /**
     * @return null|string
     */
    public function getOriginalName(): ?string;

    /**
     * @param string $originalName
     */
    public function setOriginalName(string $originalName): void;

    /**
     * @return null|string
     */
    public function getMime(): ?string;

    /**
     * @param string $mime
     */
    public function setMime(string $mime): void;

    /**
     * @param array $sizes
     */
    public function setSizes(array $sizes): void;

    /**
     * @return array
     */
    public function getSizes(): array;

    /**
     * @param string $path
     *
     * @return string
     */
    public function getResizedPath(string $path): string;

    /**
     * @param array $data
     *
     * @return FileInterface
     */
    public static function deserialize(array $data = []): self;
}
