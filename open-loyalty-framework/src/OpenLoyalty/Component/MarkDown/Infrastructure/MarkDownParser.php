<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\MarkDown\Infrastructure;

/**
 * Interface MarkDownParser.
 */
interface MarkDownParser
{
    /**
     * @param string $value
     *
     * @return string
     */
    public function parse(string $value): string;
}
