<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Level\Tests\Unit\Domain;

use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\Model\LevelPhoto;
use PHPUnit\Framework\TestCase;

/**
 * Class LevelTest.
 */
class LevelTest extends TestCase
{
    const LEVEL_ID = '3a40b784-913f-45ee-8646-a78b2b4f5cef';

    /**
     * @var Level
     */
    private $levelObject;

    public function setUp()
    {
        parent::setUp();
        $this->levelObject = new Level(new LevelId(self::LEVEL_ID), 2);

        $levelPhoto = new LevelPhoto();
        $levelPhoto->setPath('some/path/to/photo.png');
        $levelPhoto->setMime('some/mime');
        $levelPhoto->setOriginalName('photo.png');
        $this->levelObject->setPhoto($levelPhoto);
    }

    /**
     * @test
     */
    public function it_returns_true_if_photo_exists()
    {
        $this->assertTrue($this->levelObject->hasLevelPhoto());
    }

    /**
     * @test
     */
    public function it_returns_false_if_photo_does_not_exist()
    {
        $this->levelObject->removePhoto();
        $this->assertFalse($this->levelObject->hasLevelPhoto());
    }

    /**
     * @test
     */
    public function it_returns_false_if_photo_object_is_empty()
    {
        $this->levelObject->removePhoto();
        $this->levelObject->setPhoto(new LevelPhoto());
        $this->assertFalse($this->levelObject->hasLevelPhoto());
    }
}
