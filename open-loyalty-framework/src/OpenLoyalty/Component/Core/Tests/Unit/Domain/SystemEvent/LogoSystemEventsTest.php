<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Tests\Unit\Domain\SystemEvent;

use OpenLoyalty\Component\Core\Domain\SystemEvent\LogoSystemEvents;
use OpenLoyalty\Component\Core\Infrastructure\FileInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class ResizeLogoTest.
 */
class LogoSystemEventsTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_correct_event_instance()
    {
        $logoSystemEvents = new LogoSystemEvents();

        $fileMock = $this->getMockForAbstractClass(FileInterface::class);
        $type = 'small-logo';
        $resizedImages = ['192x192'];

        $event = $logoSystemEvents->createLogoResizedEventInstance($fileMock, $type, $resizedImages);
        $this->assertSame($fileMock, $event->getOriginFile());
        $this->assertSame($type, $event->getType());
        $this->assertSame($resizedImages, $event->getResizedImages());
    }
}
