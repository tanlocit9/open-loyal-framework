<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Unit\Domain\Command;

use Assert\AssertionFailedException;
use Broadway\EventDispatcher\EventDispatcher;
use OpenLoyalty\Component\Campaign\Domain\Command\RemovePhotoCommand;
use OpenLoyalty\Component\Campaign\Domain\Command\RemovePhotoCommandHandler;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Entity\CampaignPhoto;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignPhotoRepositoryInterface;
use OpenLoyalty\Component\Campaign\Domain\PhotoId;
use OpenLoyalty\Component\Campaign\Domain\PhotoMimeType;
use OpenLoyalty\Component\Campaign\Domain\PhotoOriginalName;
use OpenLoyalty\Component\Campaign\Domain\PhotoPath;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class RemoveCampaignPhotoCommandHandlerTest.
 */
final class RemovePhotoCommandHandlerTest extends TestCase
{
    private const CAMPAIGN_ID = '000096cf-32a3-43bd-9034-4df343e5fd93';
    private const CAMPAIGN_PHOTO_ID = '00000000-0000-0000-0000-4df343e5fd93';

    /**
     * @var CampaignPhotoRepositoryInterface|MockObject
     */
    private $photoRepository;

    /**
     * @var EventDispatcher|MockObject
     */
    private $eventDispatcher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->photoRepository = $this->createMock(CampaignPhotoRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
    }

    /**
     * @test
     */
    public function it_remove_photo_on_handle_command(): void
    {
        $photoId = new PhotoId(self::CAMPAIGN_PHOTO_ID);
        $campaignId = new CampaignId(self::CAMPAIGN_ID);
        $campaign = new Campaign($campaignId);

        $campaignPhoto = new CampaignPhoto(
            $campaign,
            $photoId,
            new PhotoPath('path/to/file.jpg'),
            new PhotoOriginalName('file.jpg'),
            new PhotoMimeType('image/jpg')
        );
        $this->photoRepository
            ->expects($this->once())->method('findOneByIdCampaignId')
            ->willReturn($campaignPhoto);

        $this->photoRepository->expects($this->once())->method('remove');

        $this->eventDispatcher->expects($this->once())->method('dispatch');

        $this->handleCommand($campaignId, $photoId);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_campaign_photo_not_found(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $photoId = new PhotoId(self::CAMPAIGN_PHOTO_ID);
        $campaignId = new CampaignId(self::CAMPAIGN_ID);

        $this->eventDispatcher->expects($this->never())->method('dispatch');

        $this->photoRepository
            ->expects($this->once())
            ->method('findOneByIdCampaignId')
            ->willReturn(null);

        $this->photoRepository->expects($this->never())->method('remove');
        $this->handleCommand($campaignId, $photoId);
    }

    /**
     * @param CampaignId $campaignId
     * @param PhotoId    $photoId
     *
     * @throws AssertionFailedException
     */
    private function handleCommand(CampaignId $campaignId, PhotoId $photoId): void
    {
        $handler = new RemovePhotoCommandHandler(
            $this->photoRepository,
            $this->eventDispatcher
        );
        $command = RemovePhotoCommand::byCampaignIdAndPhotoId(
            $campaignId,
            $photoId
        );
        $handler->handleRemovePhotoCommand($command);
    }
}
