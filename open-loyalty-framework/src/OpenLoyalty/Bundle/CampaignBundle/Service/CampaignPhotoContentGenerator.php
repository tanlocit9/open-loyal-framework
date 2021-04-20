<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Service;

use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\PhotoId;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignPhotoRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Gaufrette\Filesystem;

/**
 * Class CampaignPhotoContentGenerator.
 */
class CampaignPhotoContentGenerator implements CampaignPhotoContentGeneratorInterface
{
    /**
     * @var CampaignPhotoRepositoryInterface
     */
    private $photoRepository;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * CampaignPhotoContentGenerator constructor.
     *
     * @param CampaignPhotoRepositoryInterface $photoRepository
     * @param Filesystem                       $filesystem
     */
    public function __construct(CampaignPhotoRepositoryInterface $photoRepository, Filesystem $filesystem)
    {
        $this->photoRepository = $photoRepository;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhotoContent(string $campaignId, string $photoId): Response
    {
        $photo = $this->photoRepository->findOneByIdCampaignId(new PhotoId($photoId), new CampaignId($campaignId));
        if (null === $photo) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $content = $this->filesystem->get((string) $photo->getPath())->getContent();

        $response = new Response($content);
        $response->headers->set('Content-Disposition', 'inline');
        $response->headers->set('Content-Type', (string) $photo->getMimeType());
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');

        return $response;
    }
}
