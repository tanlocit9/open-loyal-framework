<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Domain\Model;

use Broadway\Serializer\Serializable;

/**
 * Class LabelMultiplier.
 */
class LabelMultiplier implements Serializable
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var float
     */
    protected $multiplier;

    /**
     * LabelMultiplier constructor.
     *
     * @param string $key
     * @param string $value
     * @param float  $multiplier
     */
    public function __construct(string $key, string $value, float $multiplier)
    {
        $this->key = $key;
        $this->value = $value;
        $this->multiplier = $multiplier;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return float
     */
    public function getMultiplier(): float
    {
        return $this->multiplier;
    }

    /**
     * @param array $data
     *
     * @return LabelMultiplier The object instance
     */
    public static function deserialize(array $data)
    {
        return new self((string) $data['key'], (string) $data['value'], (float) $data['multiplier']);
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'multiplier' => $this->multiplier,
        ];
    }
}
