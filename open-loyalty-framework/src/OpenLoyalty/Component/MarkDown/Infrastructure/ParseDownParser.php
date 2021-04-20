<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\MarkDown\Infrastructure;

use Parsedown;

/**
 * Class ParseDown.
 */
class ParseDownParser implements MarkDownParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $value): string
    {
        $parser = new Parsedown();

        return $parser->line($value);
    }
}
