<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Import\Infrastructure;

/**
 * Interface FileImporter.
 */
interface FileImporter
{
    /**
     * @param string $filePath
     *
     * @return ImportResult
     */
    public function import(string $filePath): ImportResult;
}
