<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Tests\Unit\Domain\SystemEvent;

use OpenLoyalty\Component\Core\Domain\SystemEvent\LogoResizedSystemEvent;
use OpenLoyalty\Component\Core\Infrastructure\FileInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class LogoResizedSystemEventTest.
 */
class LogoResizedSystemEventTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_right_property_values()
    {
        $fileMock = $this->getMockForAbstractClass(FileInterface::class);
        $type = 'small-logo';
        $resizedImages = ['512x512', '96x96'];
        $logoSystemEvent = new LogoResizedSystemEvent($fileMock, $type, $resizedImages);

        $this->assertSame($fileMock, $logoSystemEvent->getOriginFile());
        $this->assertSame($type, $logoSystemEvent->getType());
        $this->assertSame($resizedImages, $logoSystemEvent->getResizedImages());
    }
}
