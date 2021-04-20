<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Domain\SystemEvent;

use OpenLoyalty\Component\Core\Infrastructure\FileInterface;

/**
 * Class LogoSystemEvents.
 */
final class LogoSystemEvents
{
    const RESIZED_LOGO_EVENT = 'oloy.logo.resized';

    /**
     * @param FileInterface $logo
     * @param string        $logoType
     * @param array         $resizedImages
     *
     * @return LogoResizedSystemEvent
     */
    public function createLogoResizedEventInstance(FileInterface $logo, string $logoType, array $resizedImages = []): LogoResizedSystemEvent
    {
        return new LogoResizedSystemEvent($logo, $logoType, $resizedImages);
    }
}
