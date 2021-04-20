<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Tests\Unit\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\EventDispatcher\EventDispatcher;
use OpenLoyalty\Bundle\SettingsBundle\Entity\FileSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Model\Logo;
use OpenLoyalty\Bundle\SettingsBundle\Model\Settings;
use OpenLoyalty\Bundle\SettingsBundle\Service\LogoUploader;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Core\Domain\Command\PhotoCommandHandler;
use OpenLoyalty\Component\Core\Domain\Command\RemovePhoto;
use OpenLoyalty\Component\Core\Domain\Command\UploadPhoto;
use OpenLoyalty\Component\Core\Domain\Exception\InvalidPhotoNameException;
use OpenLoyalty\Component\Core\Domain\SystemEvent\PhotoSystemEvents;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PhotoCommandHandlerTest.
 */
class PhotoCommandHandlerTest extends TestCase
{
    /**
     * @var PhotoCommandHandler
     */
    private $handler;

    /**
     * @var MockObject
     */
    private $settingsManager;

    /**
     * @var MockObject
     */
    private $uploader;

    /**
     * @var MockObject
     */
    private $translator;

    /**
     * @var MockObject
     */
    private $dispatcher;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->uploader = $this->getMockBuilder(LogoUploader::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['isValidName'])
            ->getMock();

        $this->uploader->expects($this->any())
            ->method('upload')
            ->willReturn(new Logo());

        $this->translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $this->translator->method('trans')->willReturnArgument(0);

        $this->settingsManager = $this->getMockBuilder(SettingsManager::class)->disableOriginalConstructor()->getMock();
        $this->dispatcher = $this->getMockBuilder(EventDispatcher::class)->disableOriginalConstructor()->getMock();
        $this->handler = new PhotoCommandHandler($this->uploader, $this->settingsManager, new PhotoSystemEvents(), $this->translator, $this->dispatcher);
    }

    /**
     * @test
     */
    public function it_has_right_interface_implemented()
    {
        $this->assertInstanceOf(SimpleCommandHandler::class, $this->handler);
    }

    /**
     * @test
     */
    public function it_throw_exception_on_invalid_upload_photo_name()
    {
        $this->expectException(InvalidPhotoNameException::class);

        $photoMock = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $uploadCommand = new UploadPhoto('invalid-name', $photoMock);
        $this->handler->handleUploadPhoto($uploadCommand);
    }

    /**
     * @test
     * @dataProvider photoNamesDataProvider
     *
     * @param string $name
     *
     * @throws \Exception
     */
    public function it_uploads_on_upload_photo_command(string $name)
    {
        $this->settingsManager->expects($this->once())
            ->method('getSettings')
            ->willReturn(new Settings());

        $this->uploader->expects($this->once())->method('upload');
        $this->dispatcher->expects($this->once())->method('dispatch');
        $photoMock = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $uploadCommand = new UploadPhoto($name, $photoMock);
        $this->handler->handleUploadPhoto($uploadCommand);
    }

    /**
     * @test
     * @dataProvider photoNamesDataProvider
     *
     * @param string $name
     *
     * @throws \Exception
     */
    public function it_removes_on_remove_photo_command(string $name)
    {
        $this->settingsManager->expects($this->once())
            ->method('getSettings')
            ->willReturn(Settings::fromArray([new FileSettingEntry($name, new Logo())]));

        $this->uploader->expects($this->once())->method('remove');
        $this->dispatcher->expects($this->once())->method('dispatch');
        $removeCommand = new RemovePhoto($name);
        $this->handler->handleRemovePhoto($removeCommand);
    }

    /**
     * @return array
     */
    public function photoNamesDataProvider(): array
    {
        return [
            [LogoUploader::LOGO],
            [LogoUploader::SMALL_LOGO],
            [LogoUploader::HERO_IMAGE],
            [LogoUploader::CLIENT_COCKPIT_HERO_IMAGE],
            [LogoUploader::CLIENT_COCKPIT_LOGO_BIG],
            [LogoUploader::CLIENT_COCKPIT_LOGO_SMALL],
            [LogoUploader::ADMIN_COCKPIT_LOGO],
        ];
    }
}
