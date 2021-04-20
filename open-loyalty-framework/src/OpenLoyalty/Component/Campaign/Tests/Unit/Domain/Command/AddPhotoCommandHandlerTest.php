<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Unit\Domain\Command;

use Broadway\EventDispatcher\EventDispatcher;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Component\Campaign\Domain\Command\AddPhotoCommand;
use OpenLoyalty\Component\Campaign\Domain\Command\AddPhotoCommandHandler;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Factory\PhotoEntityFactory;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignPhotoRepositoryInterface;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class AddPhotoCommandHandlerTest.
 */
class AddPhotoCommandHandlerTest extends TestCase
{
    private const CAMPAIGN_ID = '000096cf-32a3-43bd-9034-4df343e5fd93';

    private const CAMPAIGN_PHOTO_ID = '00000000-0000-0000-0000-4df343e5fd93';

    /**
     * @var CampaignRepositoryInterface | MockObject
     */
    private $campaignRepository;

    /**
     * @var EventDispatcher | MockObject
     */
    private $eventDispatcher;

    /**
     * @var CampaignPhotoRepositoryInterface | MockObject
     */
    private $photoRepository;

    /**
     * @var UuidGeneratorInterface | MockObject
     */
    private $uuidGenerator;

    /**
     * @var AddPhotoCommandHandler
     */
    private $handler;

    /**
     * @var UploadedFile
     */
    private $uploadedFile;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->campaignRepository = $this->createMock(CampaignRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->photoRepository = $this->createMock(CampaignPhotoRepositoryInterface::class);
        $this->uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
        $this->uuidGenerator
            ->expects($this->any())
            ->method('generate')
            ->willReturn(self::CAMPAIGN_PHOTO_ID);

        $photoFactory = new PhotoEntityFactory();
        $this->handler = new AddPhotoCommandHandler(
            $this->campaignRepository,
            $this->eventDispatcher,
            $this->photoRepository,
            $photoFactory,
            $this->uuidGenerator
        );

        $this->uploadedFile = new UploadedFile(
            __DIR__.'/../fixture/add_photo_handler_sample.png',
            'add_photo_handler_sample.png',
            'image/png'
        );
    }

    /**
     * @test
     */
    public function it_add_photo_to_campaign_when_handle_command(): void
    {
        $this->campaignRepository
            ->expects($this->once())
            ->method('findOneById')
            ->willReturn(new Campaign(new CampaignId(self::CAMPAIGN_ID)));

        $this->photoRepository->expects($this->once())->method('save');

        $this->handler->handleAddPhotoCommand(
            AddPhotoCommand::withData($this->uploadedFile, new CampaignId(self::CAMPAIGN_ID))
        );
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_campaign_not_found(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->campaignRepository
            ->expects($this->once())
            ->method('findOneById')
            ->willReturn(null);

        $this->handler->handleAddPhotoCommand(
            AddPhotoCommand::withData($this->uploadedFile, new CampaignId(self::CAMPAIGN_ID))
        );
    }

    /**
     * @test
     */
    public function it_dispatch_an_event_to_save_file_on_disc(): void
    {
        $this->campaignRepository
            ->expects($this->once())
            ->method('findOneById')
            ->willReturn(new Campaign(new CampaignId(self::CAMPAIGN_ID)));

        $this->eventDispatcher->expects($this->once())->method('dispatch');
        $this->handler->handleAddPhotoCommand(
            AddPhotoCommand::withData($this->uploadedFile, new CampaignId(self::CAMPAIGN_ID))
        );
    }
}
