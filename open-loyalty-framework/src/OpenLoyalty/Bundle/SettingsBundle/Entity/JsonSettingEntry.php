<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class JsonSettingEntry.
 *
 * @ORM\Entity()
 */
class JsonSettingEntry extends SettingsEntry implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var array
     * @ORM\Column(type="json_array", name="json_value")
     */
    protected $jsonValue;

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $this->jsonValue = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $array = $this->jsonValue;

        // indicator is an array sortable
        $isSortable = false;

        usort($array, function ($a, $b) use (&$isSortable) {
            if (isset($a['priority']) && isset($b['priority'])) {
                // because array has priority field it's sortable so set to true
                $isSortable = true;

                if ($a['priority'] == $b['priority']) {
                    return 0;
                }

                return $a['priority'] < $b['priority'] ? -1 : 1;
            }

            return 0;
        });

        // if array is not sortable (doesn't have priority field) we must return
        // original value cause usort resets keys
        if (!$isSortable) {
            return $this->jsonValue;
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->jsonValue[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->jsonValue[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->jsonValue[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->jsonValue[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->jsonValue);
    }
}
