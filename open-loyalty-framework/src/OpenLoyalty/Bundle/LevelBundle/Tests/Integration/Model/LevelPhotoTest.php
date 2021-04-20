<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\LevelBundle\Tests\Integration\Model;

use OpenLoyalty\Bundle\LevelBundle\Model\LevelPhoto;
use OpenLoyalty\Component\Level\Domain\Model\LevelPhoto as DomainPhoto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LevelPhotoTest extends TestCase
{
    /** @var LevelPhoto */
    private $levelPhoto;

    public function setUp()
    {
        parent::setUp();

        $this->levelPhoto = new LevelPhoto();
    }

    /**
     * @test
     */
    public function it_has_right_interface()
    {
        $this->assertInstanceOf(DomainPhoto::class, $this->levelPhoto);
    }

    /**
     * @test
     */
    public function it_returns_right_file()
    {
        $file = new UploadedFile(__FILE__, 'original.name.png');
        $this->assertNull($this->levelPhoto->getFile());

        $this->levelPhoto->setFile($file);
        $this->assertSame($file, $this->levelPhoto->getFile());
        $this->assertEquals(__FILE__, $this->levelPhoto->getFile()->getRealPath());
        $this->assertEquals('original.name.png', $this->levelPhoto->getFile()->getClientOriginalName());
    }

    /**
     * @test
     */
    public function it_returns_right_interface()
    {
        $returned = $this->levelPhoto->setFile(new UploadedFile(__FILE__, 'some.name.png'));
        $this->assertInstanceOf(LevelPhoto::class, $returned);
    }
}
