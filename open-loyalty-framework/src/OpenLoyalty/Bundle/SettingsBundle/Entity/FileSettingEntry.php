<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenLoyalty\Bundle\SettingsBundle\Model\FileInterface;

/**
 * Class FileSettingEntry.
 *
 * @ORM\Entity()
 */
class FileSettingEntry extends SettingsEntry
{
    /**
     * @var array
     * @ORM\Column(type="json_array", name="json_value")
     */
    protected $value = [];

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        if (!$value instanceof FileInterface) {
            return;
        }

        $this->value = [
            '__class' => get_class($value),
            'path' => $value->getPath(),
            'originalName' => $value->getOriginalName(),
            'mime' => $value->getMime(),
            'sizes' => $value->getSizes(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        if (array_key_exists('__class', $this->value)
            && array_key_exists('path', $this->value)
            && array_key_exists('originalName', $this->value)
            && array_key_exists('mime', $this->value)
            && array_key_exists('sizes', $this->value)
        ) {
            $className = $this->value['__class'];

            return call_user_func(sprintf('%s::deserialize', $className), $this->value);
        }
    }
}
