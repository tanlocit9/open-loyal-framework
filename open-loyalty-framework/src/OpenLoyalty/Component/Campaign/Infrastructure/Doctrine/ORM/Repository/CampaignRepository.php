<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManagerInterface;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignRepositoryInterface;

/**
 * Class CampaignRepository.
 */
class CampaignRepository implements CampaignRepositoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * CampaignRepository constructor.
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
    public function findOneById(CampaignId $campaignId): ?Campaign
    {
        $campaign = $this->entityManager->getRepository(Campaign::class)->byId($campaignId);

        return $campaign;
    }
}
