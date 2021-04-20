<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Integration\Infrastructure\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManagerInterface;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Entity\CampaignPhoto;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignPhotoRepositoryInterface;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignRepositoryInterface;
use OpenLoyalty\Component\Campaign\Domain\PhotoId;
use OpenLoyalty\Component\Campaign\Domain\PhotoMimeType;
use OpenLoyalty\Component\Campaign\Domain\PhotoOriginalName;
use OpenLoyalty\Component\Campaign\Domain\PhotoPath;
use OpenLoyalty\Component\Campaign\Infrastructure\Doctrine\ORM\Repository\CampaignPhotoRepository;
use OpenLoyalty\Component\Campaign\Infrastructure\Doctrine\ORM\Repository\CampaignRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CampaignPhotoRepositoryTest.
 */
final class CampaignPhotoRepositoryTest extends KernelTestCase
{
    private const CAMPAIGN_ID = '000096cf-32a3-43bd-9034-4df343e5fd93';

    private const CAMPAIGN_PHOTO_ID = '00000000-0000-0000-0000-000000000001';

    /**
     * @var CampaignPhotoRepositoryInterface
     */
    private $photoRepository;

    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $this->entityManager = self::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        $this->photoRepository = new CampaignPhotoRepository($this->entityManager);
        $this->campaignRepository = new CampaignRepository($this->entityManager);
    }

    /**
     * @test
     */
    public function it_add_photo_to_campaign(): void
    {
        $campaign = $this->loadCampaign();
        $this->savePhoto($campaign);

        $photoId = new PhotoId(self::CAMPAIGN_PHOTO_ID);
        $actual = $this->photoRepository->findOneByIdCampaignId($photoId, $campaign->getCampaignId());
        $this->assertNotNull($actual);
    }

    /**
     * @test
     */
    public function it_remove_photo_from_campaign(): void
    {
        $photoId = new PhotoId(self::CAMPAIGN_PHOTO_ID);
        $campaignId = new CampaignId(self::CAMPAIGN_ID);
        $this->removePhoto($photoId, $campaignId);
        $this->assertNull($this->photoRepository->findOneByIdCampaignId($photoId, $campaignId));
    }

    /**
     * @test
     */
    public function it_return_null_when_photo_not_found_by_id(): void
    {
        $nonExistsPhotoId = new PhotoId('00000000-0000-0000-0000-000000000122');
        $campaignId = new CampaignId(self::CAMPAIGN_ID);
        $this->assertNull($this->photoRepository->findOneByIdCampaignId($nonExistsPhotoId, $campaignId));
    }

    /**
     * @test
     */
    public function it_return_photo_for_campaign(): void
    {
        $campaign = $this->loadCampaign();
        $this->savePhoto($campaign);

        $campaignId = new CampaignId(self::CAMPAIGN_ID);
        $actual = $this->photoRepository->findOneByIdCampaignId(new PhotoId(self::CAMPAIGN_PHOTO_ID), $campaignId);
        $this->assertNotNull($actual);
    }

    /**
     * @test
     */
    public function it_return_null_when_photo_for_campaign_not_found(): void
    {
        $campaign = $this->loadCampaign();
        $this->savePhoto($campaign);

        $campaignId = new CampaignId(self::CAMPAIGN_ID);
        $nonExistsPhotoId = new PhotoId('00000000-0000-0000-0000-000000000122');

        $actual = $this->photoRepository->findOneByIdCampaignId($nonExistsPhotoId, $campaignId);
        $this->assertNull($actual);
    }

    /**
     * @return Campaign
     *
     * @throws \Assert\AssertionFailedException
     */
    private function loadCampaign(): Campaign
    {
        $campaign = $this->campaignRepository->findOneById(new CampaignId(self::CAMPAIGN_ID));

        return $campaign;
    }

    /**
     * @param Campaign $campaign
     */
    private function savePhoto(Campaign $campaign): void
    {
        $photoPath = new PhotoPath('/path/to/photo/');
        $originalName = new PhotoOriginalName('photo1.jpg');
        $mimeType = new PhotoMimeType('image/jpg');
        $photoId = new PhotoId(self::CAMPAIGN_PHOTO_ID);
        $this->photoRepository->save(new CampaignPhoto($campaign, $photoId, $photoPath, $originalName, $mimeType));
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->removePhoto(new PhotoId(self::CAMPAIGN_PHOTO_ID), new CampaignId(self::CAMPAIGN_ID));
    }

    /**
     * @param PhotoId    $photoId
     * @param CampaignId $campaignId
     */
    private function removePhoto(PhotoId $photoId, CampaignId $campaignId): void
    {
        $photo = $this->photoRepository->findOneByIdCampaignId($photoId, $campaignId);
        if (null == $photo) {
            return;
        }
        $this->photoRepository->remove($photo);
    }
}
