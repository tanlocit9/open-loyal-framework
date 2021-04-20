<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use Doctrine\ORM\OptimisticLockException;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;

/**
 * Class CampaignCommandHandler.
 */
class CampaignCommandHandler extends SimpleCommandHandler
{
    /**
     * @var CampaignRepository
     */
    protected $campaignRepository;

    /**
     * CampaignCommandHandler constructor.
     *
     * @param CampaignRepository $campaignRepository
     */
    public function __construct(CampaignRepository $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    public function handleCreateCampaign(CreateCampaign $command)
    {
        $data = $command->getCampaignData();
        Campaign::validateRequiredData($data);
        $campaign = new Campaign($command->getCampaignId(), $data);
        $this->campaignRepository->save($campaign);
    }

    public function handleUpdateCampaign(UpdateCampaign $command)
    {
        $data = $command->getCampaignData();
        Campaign::validateRequiredData($data);
        /** @var Campaign $campaign */
        $campaign = $this->campaignRepository->byId($command->getCampaignId());
        $campaign->setFromArray($command->getCampaignData());

        $this->campaignRepository->save($campaign);
    }

    public function handleChangeCampaignState(ChangeCampaignState $command)
    {
        /** @var Campaign $campaign */
        $campaign = $this->campaignRepository->byId($command->getCampaignId());
        $campaign->setActive($command->getActive());

        $this->campaignRepository->save($campaign);
    }

    /**
     * @param SetCampaignBrandIcon $command
     *
     * @throws OptimisticLockException
     */
    public function handleSetCampaignBrandIcon(SetCampaignBrandIcon $command)
    {
        /** @var Campaign $campaign */
        $campaign = $this->campaignRepository->byId($command->getCampaignId());
        $campaign->setCampaignBrandIcon($command->getCampaignBrandIcon());

        $this->campaignRepository->save($campaign);
    }

    /**
     * @param RemoveCampaignBrandIcon $command
     *
     * @throws OptimisticLockException
     */
    public function handleRemoveCampaignBrandIcon(RemoveCampaignBrandIcon $command)
    {
        /** @var Campaign $campaign */
        $campaign = $this->campaignRepository->byId($command->getCampaignId());
        $campaign->setCampaignBrandIcon(null);

        $this->campaignRepository->save($campaign);
    }
}
