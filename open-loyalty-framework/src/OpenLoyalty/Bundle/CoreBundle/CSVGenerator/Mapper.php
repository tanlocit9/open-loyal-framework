<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CoreBundle\CSVGenerator;

/**
 * Class Mapper.
 */
class Mapper implements MapperInterface
{
    private $map;

    /**
     * Mapper constructor.
     *
     * @param array $map
     *
     * @throws \Exception
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * {@inheritdoc}
     */
    public function map(string $field, $value = null)
    {
        if (empty($value)) {
            return $value;
        }
        if (key_exists($field, $this->map) && method_exists($this, $this->map[$field]['conversion'])) {
            return call_user_func_array([$this, $this->map[$field]['conversion']], [$value]);
        }

        return $value;
    }

    /**
     * @param int $time
     *
     * @return string
     */
    public function timeToString(int $time): string
    {
        return date('Y-m-d H:i:s', $time);
    }

    /**
     * @param bool $bool
     *
     * @return string
     */
    public function boolToString(bool $bool): string
    {
        return (string) $bool;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(array $map): MapperInterface
    {
        return new self($map);
    }
}
