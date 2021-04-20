<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Infrastructure\Provider;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;

/**
 * Class RewardCampaignProvider.
 */
class RewardCampaignProvider implements RewardCampaignProviderInterface
{
    /**
     * @var CampaignRepository
     */
    private $repository;

    /**
     * RewardCampaignProvider constructor.
     *
     * @param CampaignRepository $repository
     */
    public function __construct(CampaignRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function findById(CampaignId $campaignId): Campaign
    {
        $campaign = $this->repository->byId($campaignId);
        if (null === $campaign) {
            throw new \InvalidArgumentException('Campaign not found by given id!');
        }

        return $campaign;
    }
}
