<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\SettingsBundle\EventSubscriber;

use OpenLoyalty\Bundle\SettingsBundle\Service\GeneralSettingsManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class TimezoneSubscriber.
 */
class TimezoneSubscriber implements EventSubscriberInterface
{
    /**
     * @var GeneralSettingsManagerInterface
     */
    private $settingsManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TimezoneSubscriber constructor.
     *
     * @param GeneralSettingsManagerInterface $settingsManager
     * @param LoggerInterface                 $logger
     */
    public function __construct(GeneralSettingsManagerInterface $settingsManager, LoggerInterface $logger)
    {
        $this->settingsManager = $settingsManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => [['onCommand', 0]],
            KernelEvents::REQUEST => [['onRequest', 0]],
        ];
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onCommand(ConsoleCommandEvent $event): void
    {
        $this->setCurrentTimezone();
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event): void
    {
        $this->setCurrentTimezone();
    }

    /**
     * Set timezone from settings as current timezone.
     */
    protected function setCurrentTimezone(): void
    {
        $timezone = $this->getSettingsTimezone();
        if ($timezone) {
            date_default_timezone_set($timezone);
        }
    }

    /**
     * @return string
     */
    protected function getSettingsTimezone(): ?string
    {
        try {
            return $this->settingsManager->getTimezone();
        } catch (\Throwable $ex) {
            $this->logger->warning('Timezone from configuration has not been initialized');
        }

        return null;
    }
}
