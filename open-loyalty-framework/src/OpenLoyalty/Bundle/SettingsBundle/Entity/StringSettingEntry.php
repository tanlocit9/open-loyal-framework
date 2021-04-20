<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class StringSettingEntry.
 *
 * @ORM\Entity()
 */
class StringSettingEntry extends SettingsEntry
{
    /**
     * @var string
     * @ORM\Column(type="string", name="string_value", length=10240)
     */
    protected $stringValue;

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->stringValue = (string) $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->stringValue;
    }
}
