<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Model;

use OpenLoyalty\Bundle\SettingsBundle\Entity\SettingsEntry;

/**
 * Class Settings.
 */
class Settings
{
    /**
     * @var SettingsEntry[]
     */
    protected $entries = [];

    /**
     * @param string $name
     *
     * @return SettingsEntry|null
     */
    public function __get($name)
    {
        return $this->getEntry($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->addEntry($value);
    }

    /**
     * Remove entry.
     *
     * @param string $key
     *
     * @return bool return true if success, otherwise false
     */
    public function removeEntry($key)
    {
        if (isset($this->entries[$key])) {
            unset($this->entries[$key]);

            return true;
        }

        return false;
    }

    /**
     * @param SettingsEntry $entry
     */
    public function addEntry(SettingsEntry $entry)
    {
        $this->entries[$entry->getKey()] = $entry;
    }

    /**
     * @param string $key
     *
     * @return SettingsEntry|null
     */
    public function getEntry($key)
    {
        if (!isset($this->entries[$key])) {
            return null;
        }

        return $this->entries[$key];
    }

    /**
     * @return SettingsEntry[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param array $entries
     *
     * @return Settings
     */
    public static function fromArray(array $entries)
    {
        $settings = new self();
        foreach ($entries as $entry) {
            if ($entry instanceof SettingsEntry) {
                $settings->addEntry($entry);
            }
        }

        return $settings;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $ret = [];
        foreach ($this->entries as $entry) {
            $ret[$entry->getKey()] = $entry->getValue();
        }

        return $ret;
    }
}
