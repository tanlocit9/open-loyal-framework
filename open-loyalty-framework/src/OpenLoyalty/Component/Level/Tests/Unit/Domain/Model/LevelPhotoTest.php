<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Level\Tests\Unit\Domain\Model;

use OpenLoyalty\Component\Level\Domain\Model\LevelPhoto;
use PHPUnit\Framework\TestCase;

class LevelPhotoTest extends TestCase
{
    /**
     * @var LevelPhoto
     */
    private $levelPhoto;

    public function setUp()
    {
        $this->levelPhoto = new LevelPhoto();
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_has_right_path()
    {
        $path = 'Some\Sample\Path';
        $this->assertNull($this->levelPhoto->getPath());
        $this->levelPhoto->setPath($path);
        $this->assertSame($path, $this->levelPhoto->getPath());
    }

    /**
     * @test
     */
    public function it_has_right_name()
    {
        $originalName = 'original.name.png';
        $this->assertNull($this->levelPhoto->getOriginalName());
        $this->levelPhoto->setOriginalName($originalName);
        $this->assertSame($originalName, $this->levelPhoto->getOriginalName());
    }

    /**
     * @test
     */
    public function it_has_right_mime()
    {
        $mime = 'image/png';
        $this->assertNull($this->levelPhoto->getMime());
        $this->levelPhoto->setMime($mime);
        $this->assertSame($mime, $this->levelPhoto->getMime());
    }
}
