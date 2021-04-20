<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CoreBundle\Service;

use OpenLoyalty\Bundle\CoreBundle\CSVGenerator\MapperInterface;

/**
 * Interface GeneratorInterface.
 */
interface GeneratorInterface
{
    /**
     * @param iterable $rows
     * @param array    $headers
     * @param array    $fields
     *
     * @return mixed
     */
    public function generate(iterable $rows, array $headers = [], array $fields = []);

    /**
     * @param MapperInterface $mapper
     *
     * @return mixed
     */
    public function setCustomMapper(MapperInterface $mapper);
}
