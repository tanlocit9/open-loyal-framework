<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\EventListener;

/**
 * Class DeleteFileListener.
 */
interface DeleteFileListenerInterface
{
    /**
     * @param string $filePath
     */
    public function __invoke(string $filePath): void;
}
