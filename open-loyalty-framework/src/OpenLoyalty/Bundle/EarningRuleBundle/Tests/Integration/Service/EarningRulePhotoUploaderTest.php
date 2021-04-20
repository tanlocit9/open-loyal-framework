<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Tests\Integration\Service;

use Gaufrette\Filesystem;
use OpenLoyalty\Bundle\EarningRuleBundle\Service\EarningRulePhotoUploader;
use OpenLoyalty\Component\EarningRule\Domain\Model\EarningRulePhoto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class EarningRulePhotoUploaderTest.
 */
class EarningRulePhotoUploaderTest extends TestCase
{
    /**
     * @var EarningRulePhotoUploader
     */
    private $earningRulePhotoUploader;

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

        $this->earningRulePhotoUploader = new EarningRulePhotoUploader($this->filesystem);
    }

    /**
     * @test
     */
    public function it_returns_right_content()
    {
        $earningRulePhoto = new EarningRulePhoto();
        $this->assertEmpty($this->earningRulePhotoUploader->get($earningRulePhoto));
        $earningRulePhoto->setPath('/tmp/file.png');
        $this->assertContains('SOMEDATA', $this->earningRulePhotoUploader->get($earningRulePhoto));
    }

    /**
     * @test
     */
    public function it_uploads_successfully()
    {
        $uploadedFile = new UploadedFile('/tmp/file.png', basename(__FILE__), 'image/png', 200, null, true);
        $returnedFile = $this->earningRulePhotoUploader->upload($uploadedFile);

        $this->assertInstanceOf(EarningRulePhoto::class, $returnedFile);
        $this->assertEquals($returnedFile->getMime(), 'image/png');
        $this->assertContains('earning_rule_photos', $returnedFile->getPath());
        $this->assertEquals($returnedFile->getOriginalName(), basename(__FILE__));
    }
}
