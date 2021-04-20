<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Import\Infrastructure;

use Prewk\XmlStringStreamer;

/**
 * Interface XMLStreamer.
 */
interface XMLFileStreamer
{
    /**
     * @param string $filePath
     *
     * @return XmlStringStreamer
     */
    public function getStreamer(string $filePath): XmlStringStreamer;
}
