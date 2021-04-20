<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManagerInterface;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Entity\CampaignPhoto;
use OpenLoyalty\Component\Campaign\Domain\PhotoId;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignPhotoRepositoryInterface;

/**
 * Class CampaignPhotoRepository.
 */
class CampaignPhotoRepository implements CampaignPhotoRepositoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * CampaignPhotoRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CampaignPhoto $photo): void
    {
        $this->entityManager->persist($photo);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(CampaignPhoto $photo): void
    {
        $this->entityManager->remove($photo);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdCampaignId(PhotoId $photoId, CampaignId $campaignId): ?CampaignPhoto
    {
        return $this
            ->entityManager
            ->getRepository(CampaignPhoto::class)
            ->findOneBy(['campaign' => $campaignId, 'photoId' => $photoId]);
    }
}
