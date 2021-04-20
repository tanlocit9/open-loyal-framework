<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Event\Listener;

use OpenLoyalty\Bundle\SettingsBundle\Entity\FileSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Core\Domain\SystemEvent\LogoResizedSystemEvent;

/**
 * Class LogoResizedListener.
 */
class LogoResizedListener
{
    /**
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * LogoResizedListener constructor.
     *
     * @param SettingsManager $manager
     */
    public function __construct(SettingsManager $manager)
    {
        $this->settingsManager = $manager;
    }

    /**
     * @param LogoResizedSystemEvent $event
     */
    public function onChange(LogoResizedSystemEvent $event)
    {
        $logo = $event->getOriginFile();
        $type = $event->getType();
        $sizes = $event->getResizedImages();
        $settings = $this->settingsManager->getSettings();
        $this->settingsManager->removeSettingByKey($type);
        $logo->setSizes($sizes);
        $settings->addEntry(new FileSettingEntry($type, $logo));
        $this->settingsManager->save($settings);
    }
}
