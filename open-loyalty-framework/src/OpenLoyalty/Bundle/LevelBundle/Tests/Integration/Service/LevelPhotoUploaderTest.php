<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\LevelBundle\Tests\Integration\Service;

use Gaufrette\Filesystem;
use OpenLoyalty\Bundle\LevelBundle\Service\LevelPhotoUploader;
use OpenLoyalty\Component\Level\Domain\Model\LevelPhoto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class LevelPhotoUploaderTest.
 */
class LevelPhotoUploaderTest extends TestCase
{
    /**
     * @var LevelPhotoUploader
     */
    private $levelPhotoUploader;

    /**
     * @var MockObject
     */
    private $filesystem;

    /**
     * @var File
     */
    private $file;

    public function setUp()
    {
        parent::setUp();
        touch('/tmp/file.png');
        $this->filesystem = $this->getMockBuilder(Filesystem::class)->disableOriginalConstructor()->getMock();
        $this->file = new \Gaufrette\File('/tmp/file.png', $this->filesystem);
        $this->filesystem
            ->method('get')
            ->willReturnReference($this->file);
        $this->filesystem->expects($this->any())
            ->method('read')
            ->will($this->returnValue('SOMEDATA'));

        $this->levelPhotoUploader = new LevelPhotoUploader($this->filesystem);
    }

    /**
     * @test
     */
    public function it_gets_right_content()
    {
        $levelPhoto = new LevelPhoto();
        $this->assertEmpty($this->levelPhotoUploader->get($levelPhoto));
        $levelPhoto->setPath('/tmp/file.png');
        $this->assertContains('SOMEDATA', $this->levelPhotoUploader->get($levelPhoto));
    }

    /**
     * @test
     */
    public function it_uploads_file_successfully()
    {
        $uploadedFile = new UploadedFile('/tmp/file.png', basename(__FILE__), 'image/png', 200, null, true);
        $returnedFile = $this->levelPhotoUploader->upload($uploadedFile);

        $this->assertInstanceOf(LevelPhoto::class, $returnedFile);
        $this->assertEquals($returnedFile->getMime(), 'image/png');
        $this->assertContains('level_photos', $returnedFile->getPath());
        $this->assertEquals($returnedFile->getOriginalName(), basename(__FILE__));
    }
}
