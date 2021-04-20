<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Import\Infrastructure;

/**
 * Interface ImporterProcessor.
 */
interface ImporterProcessor
{
    /**
     * @param mixed $entity
     *
     * @throws \Exception
     *
     * @return ProcessImportResult
     */
    public function processItem($entity): ProcessImportResult;
}
