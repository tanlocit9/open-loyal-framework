<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Tests\Unit\Service;

use Gumlet\ImageResize;
use OpenLoyalty\Bundle\SettingsBundle\Model\Logo;
use OpenLoyalty\Bundle\SettingsBundle\Service\ImageResizer;
use OpenLoyalty\Component\Core\Infrastructure\ImageResizerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ImageResizerTest.
 */
class ImageResizerTest extends TestCase
{
    /**
     * @var ImageResizer|MockObject
     */
    private $resizer;
    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->filesystem = $this->getMockBuilder(Filesystem::class)->disableOriginalConstructor()->getMock();
        $this->filesystem->expects($this->any())
            ->method('exists')
            ->willReturn(true);

        $directory = 'some/dir';
        $config = [
          'small_logo' => [
              'sizes' => [
                    ['width' => 192, 'height' => 192],
                    ['width' => 512, 'height' => 512],
                  ],
              ],
            'logo' => [
                'sizes' => [
                    ['width' => 128, 'height' => 128],
                    ['width' => 320, 'height' => 320],
                ],
            ],
        ];

        $this->resizer = $this->getMockBuilder(ImageResizer::class)
            ->setConstructorArgs([$this->filesystem, $directory, $config])
            ->setMethods(['createResizeInstance'])
            ->getMock();

        $resizeImage = $this->getMockBuilder(ImageResize::class)->disableOriginalConstructor()->getMock();
        $this->resizer->expects($this->any())->method('createResizeInstance')->willReturn($resizeImage);
    }

    /**
     * @test
     */
    public function it_implements_right_interface()
    {
        $this->assertInstanceOf(ImageResizerInterface::class, $this->resizer);
    }

    /**
     * @test
     */
    public function it_resizes_different_image_sizes()
    {
        $logo = new Logo();
        $logo->setPath('some/path');
        $resizedSmallLogo = $this->resizer->resize($logo, 'small-logo');

        $this->assertArrayHasKey('192x192', $resizedSmallLogo);
        $this->assertArrayHasKey('512x512', $resizedSmallLogo);

        $resizedLogo = $this->resizer->resize($logo, 'logo');

        $this->assertArrayHasKey('128x128', $resizedLogo);
        $this->assertArrayHasKey('320x320', $resizedLogo);
    }

    /**
     * @test
     */
    public function it_overwrites_mapping()
    {
        $newConfig = [
            'small_logo' => [
                'sizes' => [
                    ['width' => 111, 'height' => 222],
                    ['width' => 333, 'height' => 444],
                ],
            ],
            'logo' => [
                'sizes' => [
                    ['width' => 555, 'height' => 666],
                    ['width' => 777, 'height' => 888],
                ],
            ],
        ];

        $this->resizer->setMap($newConfig);
        $smallLogo = $this->resizer->resize(new Logo(), 'small-logo');
        $this->assertArrayHasKey('111x222', $smallLogo);
        $this->assertArrayHasKey('333x444', $smallLogo);

        $logo = $this->resizer->resize(new Logo(), 'logo');
        $this->assertArrayHasKey('555x666', $logo);
        $this->assertArrayHasKey('777x888', $logo);
    }
}
