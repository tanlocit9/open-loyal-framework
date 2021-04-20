<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Tests\Integration\Domain\Command;

use Broadway\CommandHandling\CommandBus;
use Doctrine\ORM\EntityManagerInterface;
use Gaufrette\Filesystem;
use OpenLoyalty\Component\Campaign\Domain\Command\RemovePhotoCommand;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Entity\CampaignPhoto;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignPhotoRepositoryInterface;
use OpenLoyalty\Component\Campaign\Domain\PhotoId;
use OpenLoyalty\Component\Campaign\Domain\PhotoMimeType;
use OpenLoyalty\Component\Campaign\Domain\PhotoOriginalName;
use OpenLoyalty\Component\Campaign\Domain\PhotoPath;
use OpenLoyalty\Component\Campaign\Infrastructure\Doctrine\ORM\Repository\CampaignPhotoRepository;
use OpenLoyalty\Component\Campaign\Infrastructure\Doctrine\ORM\Repository\CampaignRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RemovePhotoCommandHandlerTest.
 */
final class RemovePhotoCommandHandlerTest extends KernelTestCase
{
    private const CAMPAIGN_ID = '000096cf-32a3-43bd-9034-4df343e5fd93';
    private const CAMPAIGN_PHOTO_ID = '00000000-0000-0000-0000-000000000001';
    private const CAMPAIGN_PHOTO_DIR = '/uploads/tests/campaign_photos/';

    /**
     * @var CampaignPhotoRepositoryInterface
     */
    private $photoRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var CampaignRepository
     */
    private $campaignRepository;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $kernelRootDir;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        static::bootKernel();
        $this->entityManager = self::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        $this->photoRepository = new CampaignPhotoRepository($this->entityManager);

        $this->commandBus = self::$kernel->getContainer()->get('broadway.command_handling.command_bus');

        $this->campaignRepository = new CampaignRepository($this->entityManager);
        $this->fileSystem = self::$kernel->getContainer()->get('oloy.campaign.photos_filesystem');
        $this->kernelRootDir = self::$kernel->getContainer()->getParameter('kernel.root_dir');
        $this->addPhoto();
    }

    /**
     * @test
     */
    public function it_delete_photo_file_from_disc_after_dispatch_command(): void
    {
        $this->assertFileExists($this->kernelRootDir.self::CAMPAIGN_PHOTO_DIR.'remove_test.jpg');
        $this->commandBus->dispatch(RemovePhotoCommand::byCampaignIdAndPhotoId(
            new CampaignId(self::CAMPAIGN_ID),
            new PhotoId(self::CAMPAIGN_PHOTO_ID)
        ));
        $this->assertFileNotExists($this->kernelRootDir.self::CAMPAIGN_PHOTO_DIR.'remove_test.jpg');
    }

    /**
     * @test
     */
    public function it_remove_photo_from_campaign(): void
    {
        $photoId = new PhotoId(self::CAMPAIGN_PHOTO_ID);
        $campaignId = new CampaignId(self::CAMPAIGN_ID);
        $this->commandBus->dispatch(RemovePhotoCommand::byCampaignIdAndPhotoId(
            $campaignId,
            $photoId
        ));
        $result = $this->photoRepository->findOneByIdCampaignId($photoId, $campaignId);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_remove_photo_from_campaign_and_live_campaign(): void
    {
        $photoId = new PhotoId(self::CAMPAIGN_PHOTO_ID);
        $campaignId = new CampaignId(self::CAMPAIGN_ID);
        $this->commandBus->dispatch(RemovePhotoCommand::byCampaignIdAndPhotoId(
            $campaignId,
            $photoId
        ));
        $result = $this->photoRepository->findOneByIdCampaignId($photoId, $campaignId);
        $this->assertNull($result);
        $result = $this->campaignRepository->findOneById($campaignId);
        $this->assertNotNull($result);
    }

    private function addPhoto(): void
    {
        $campaign = $this->campaignRepository->findOneById(new CampaignId(self::CAMPAIGN_ID));

        $photoPath = new PhotoPath('remove_test.jpg');
        $originalName = new PhotoOriginalName('remove_photo_sample.png');
        $mimeType = new PhotoMimeType('image/jpg');
        $photoId = new PhotoId(self::CAMPAIGN_PHOTO_ID);
        $this->photoRepository->save(new CampaignPhoto($campaign, $photoId, $photoPath, $originalName, $mimeType));

        $file = 'campaign_photos/remove_test.jpg';
        $this->fileSystem->write($file, file_get_contents(__DIR__.'/../fixture/remove_photo_sample.png'), true);
    }
}
