<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Level\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Level\Domain\Command\LevelCommand;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\Command\RemoveLevelPhoto;
use PHPUnit\Framework\TestCase;

/**
 * Class RemoveLevelPhotoTest.
 */
class RemoveLevelPhotoTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_right_interface()
    {
        $this->assertInstanceOf(LevelCommand::class, new RemoveLevelPhoto(new LevelId('a8dcb15f-2b71-444c-8265-664dd930d955')));
    }

    /**
     * @test
     */
    public function it_returns_right_earning_rule()
    {
        $uuid = 'a8dcb15f-2b71-444c-8265-664dd930d955';
        $removeCommand = new RemoveLevelPhoto(new LevelId($uuid));
        $this->assertSame($uuid, $removeCommand->getLevelId()->__toString());
    }
}
