<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\EventListener;

/**
 * Class SaveFileListenerInterface.
 */
interface SaveFileListenerInterface
{
    /**
     * @param string $file
     * @param string $realPath
     */
    public function __invoke(string $file, string $realPath): void;
}
