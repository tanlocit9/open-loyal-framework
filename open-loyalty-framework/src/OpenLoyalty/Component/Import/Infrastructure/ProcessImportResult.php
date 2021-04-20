<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Import\Infrastructure;

/**
 * Class ProcessImportResult.
 */
class ProcessImportResult
{
    /**
     * @var mixed
     */
    private $object;

    /**
     * ProcessImportResult constructor.
     *
     * @param mixed $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    public function __toString()
    {
        return (string) $this->object;
    }
}
