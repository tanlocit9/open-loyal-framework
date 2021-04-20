<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UtilityBundle\Tests\Integration\Traits;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class UploadedFileTrait.
 */
trait UploadedFileTrait
{
    /**
     * @param string $content
     * @param string $originalName
     * @param string $mimeType
     * @param int    $error
     *
     * @return UploadedFile
     */
    public function createUploadedFile(string $content, string $originalName, string $mimeType, int $error): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), uniqid());
        file_put_contents($path, $content);

        return new UploadedFile($path, $originalName, $mimeType, filesize($path), $error, true);
    }
}
