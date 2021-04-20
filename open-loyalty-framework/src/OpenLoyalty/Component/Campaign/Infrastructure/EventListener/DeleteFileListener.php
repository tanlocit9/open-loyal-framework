<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\EventListener;

use Gaufrette\Filesystem;

/**
 * Class DeleteFileListener.
 */
class DeleteFileListener implements DeleteFileListenerInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

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
     * @param string $filePath
     */
    public function __invoke(string $filePath): void
    {
        $this->filesystem->delete($filePath);
    }
}
