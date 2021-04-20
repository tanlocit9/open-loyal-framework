<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Level\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Level\Domain\Command\SetLevelPhoto;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\Model\LevelPhoto;
use PHPUnit\Framework\TestCase;

/**
 * Class SetLevelPhotoTest.
 */
class SetLevelPhotoTest extends TestCase
{
    const SAMPLE_UUID = '6d744583-4f8a-417a-9d00-289ce9aca8e1';

    /**
     * @var SetLevelPhoto
     */
    private $setLevelPhoto;

    public function setUp()
    {
        parent::setUp();
        $levelId = new LevelId(self::SAMPLE_UUID);
        $levelPhoto = new LevelPhoto();
        $this->setLevelPhoto = new SetLevelPhoto($levelId, $levelPhoto);
    }

    /**
     * @test
     */
    public function it_has_right_interface()
    {
        $this->assertInstanceOf(LevelPhoto::class, $this->setLevelPhoto->getLevelPhoto());
    }

    /**
     * @test
     */
    public function it_returns_right_level()
    {
        $this->assertSame(self::SAMPLE_UUID, $this->setLevelPhoto->getLevelId()->__toString());
    }
}
