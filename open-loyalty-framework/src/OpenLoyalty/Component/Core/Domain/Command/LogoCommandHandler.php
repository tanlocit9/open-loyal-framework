<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\EventDispatcher\EventDispatcher;
use OpenLoyalty\Bundle\SettingsBundle\Service\ImageResizer;
use OpenLoyalty\Component\Core\Domain\SystemEvent\LogoSystemEvents;

/**
 * Class LogoCommandHandler.
 */
class LogoCommandHandler extends SimpleCommandHandler
{
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var LogoSystemEvents
     */
    protected $logoSystemEvents;

    /**
     * @var ImageResizer
     */
    protected $resizer;

    /**
     * LogoCommandHandler constructor.
     *
     * @param EventDispatcher|null $eventDispatcher
     * @param LogoSystemEvents     $events
     * @param ImageResizer         $resizer
     */
    public function __construct(EventDispatcher $eventDispatcher = null, LogoSystemEvents $events, ImageResizer $resizer)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logoSystemEvents = $events;
        $this->resizer = $resizer;
    }

    /**
     * @param ResizeLogo $command
     *
     * @throws \Exception
     */
    public function handleResizeLogo(ResizeLogo $command)
    {
        $logo = $command->getLogo();
        $logoType = $command->getType();

        $resized = $this->resizer->resize($logo, $logoType);

        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(
                LogoSystemEvents::RESIZED_LOGO_EVENT,
                [
                    $this->logoSystemEvents->createLogoResizedEventInstance(
                        $command->getLogo(),
                        $logoType,
                        array_keys($resized)
                    ),
                ]
            );
        }
    }
}
