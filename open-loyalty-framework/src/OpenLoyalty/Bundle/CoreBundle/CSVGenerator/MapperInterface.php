<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CoreBundle\CSVGenerator;

/**
 * Class Mapper.
 */
interface MapperInterface
{
    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return mixed
     */
    public function map(string $field, $value = null);

    /**
     * @param array $map
     *
     * @return MapperInterface
     */
    public static function create(array $map): self;
}
