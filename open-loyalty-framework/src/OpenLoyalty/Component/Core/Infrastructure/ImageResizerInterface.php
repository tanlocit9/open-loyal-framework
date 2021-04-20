<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Infrastructure;

/**
 * Interface ImageResizerInterface.
 */
interface ImageResizerInterface
{
    /**
     * @param FileInterface $file
     * @param string        $type
     *
     * @return array
     */
    public function resize(FileInterface $file, string $type): array;

    /**
     * @param array $map
     */
    public function setMap(array $map);
}
