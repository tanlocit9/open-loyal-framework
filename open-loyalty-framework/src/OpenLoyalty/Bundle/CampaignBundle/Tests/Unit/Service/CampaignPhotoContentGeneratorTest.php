<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\CampaignBundle\Tests\Unit\Service;

use Gaufrette\File;
use Gaufrette\Filesystem;
use OpenLoyalty\Bundle\CampaignBundle\Service\CampaignPhotoContentGenerator;
use OpenLoyalty\Component\Campaign\Domain\Entity\CampaignPhoto;
use OpenLoyalty\Component\Campaign\Domain\PhotoMimeType;
use OpenLoyalty\Component\Campaign\Domain\PhotoPath;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignPhotoRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CampaignPhotoContentGeneratorTest.
 */
final class CampaignPhotoContentGeneratorTest extends TestCase
{
    private const PHOTO_ID = '00000000-0000-0000-0000-000000000001';

    private const CAMPAIGN_ID = '000096cf-32a3-43bd-9034-4df343e5fd93';

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var CampaignPhotoRepositoryInterface|MockObject
     */
    private $campaignPhotoEntity;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->campaignPhotoEntity = $this->createMock(CampaignPhoto::class);
        $this->campaignPhotoEntity->method('getPath')->willReturn(new PhotoPath('path/to/photo.jpg'));
        $this->campaignPhotoEntity->method('getMimeType')->willReturn(new PhotoMimeType('image/jpg'));

        $file = $this->createMock(File::class);
        $file->method('getContent')->willReturn('file_content');

        $this->filesystem = $this->createMock(Filesystem::class);
        $this->filesystem->method('get')->willReturn($file);
    }

    /**
     * @test
     */
    public function it_return_response_with_photo_content(): void
    {
        /** @var CampaignPhotoRepositoryInterface|MockObject $photoRepository */
        $photoRepository = $this->createMock(CampaignPhotoRepositoryInterface::class);
        $photoRepository->method('findOneByIdCampaignId')->willReturn($this->campaignPhotoEntity);

        $service = new CampaignPhotoContentGenerator($photoRepository, $this->filesystem);
        $response = $service->getPhotoContent(self::CAMPAIGN_ID, self::PHOTO_ID);
        $this->assertResponse($response);
    }

    /**
     * @test
     */
    public function it_return_404_response_when_file_not_found(): void
    {
        /** @var CampaignPhotoRepositoryInterface|MockObject $photoRepository */
        $photoRepository = $this->createMock(CampaignPhotoRepositoryInterface::class);
        $photoRepository->method('findOneByIdCampaignId')->willReturn(null);

        $service = new CampaignPhotoContentGenerator($photoRepository, $this->filesystem);
        $response = $service->getPhotoContent(self::CAMPAIGN_ID, self::PHOTO_ID);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @param Response $response
     */
    private function assertResponse(Response $response): void
    {
        $this->assertSame('file_content', $response->getContent());
        $this->assertSame('must-revalidate, no-cache, no-store, private', $response->headers->get('Cache-Control'));
        $this->assertSame('image/jpg', $response->headers->get('Content-Type'));
    }
}
