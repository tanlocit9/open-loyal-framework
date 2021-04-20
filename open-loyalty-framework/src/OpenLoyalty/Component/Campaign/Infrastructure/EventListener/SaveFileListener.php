<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\EventListener;

use Gaufrette\Filesystem;

/**
 * Class SaveFileListener.
 */
class SaveFileListener implements SaveFileListenerInterface
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
     * @param string $file
     * @param string $realPath
     */
    public function __invoke(string $file, string $realPath): void
    {
        $this->filesystem->write($file, file_get_contents($realPath));
    }
}
