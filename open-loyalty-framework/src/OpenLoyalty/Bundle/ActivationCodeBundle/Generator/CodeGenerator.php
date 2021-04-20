<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Generator;

/**
 * Interface CodeGenerator.
 */
interface CodeGenerator
{
    /**
     * Generate a code.
     *
     * @param string $objectType
     * @param string $objectId
     * @param int    $length
     *
     * @return string|int
     */
    public function generate(string $objectType, string $objectId, int $length);
}
