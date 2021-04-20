<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategory;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryRepository;

/**
 * Class CampaignCategoryCommandHandler.
 */
class CampaignCategoryCommandHandler extends SimpleCommandHandler
{
    /**
     * @var CampaignCategoryRepository
     */
    protected $campaignCategoryRepository;

    /**
     * CampaignCategoryCommandHandler constructor.
     *
     * @param CampaignCategoryRepository $campaignCategoryRepository
     */
    public function __construct(CampaignCategoryRepository $campaignCategoryRepository)
    {
        $this->campaignCategoryRepository = $campaignCategoryRepository;
    }

    /**
     * @param CreateCampaignCategory $command
     *
     * @throws \Assert\AssertionFailedException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handleCreateCampaignCategory(CreateCampaignCategory $command): void
    {
        $data = $command->getCampaignCategoryData();
        CampaignCategory::validateRequiredData($data);

        $campaignCategory = new CampaignCategory($command->getCampaignCategoryId(), $data);
        $this->campaignCategoryRepository->save($campaignCategory);
    }

    /**
     * @param UpdateCampaignCategory $command
     *
     * @throws \Assert\AssertionFailedException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handleUpdateCampaignCategory(UpdateCampaignCategory $command): void
    {
        $data = $command->getCampaignCategoryData();
        CampaignCategory::validateRequiredData($data);

        $campaignCategory = $this->campaignCategoryRepository->byId($command->getCampaignCategoryId());
        $campaignCategory->setFromArray($command->getCampaignCategoryData());
        $this->campaignCategoryRepository->save($campaignCategory);
    }

    /**
     * @param ChangeCampaignCategoryState $command
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handleChangeCampaignCategoryState(ChangeCampaignCategoryState $command): void
    {
        $campaignCategory = $this->campaignCategoryRepository->byId($command->getCampaignCategoryId());
        $campaignCategory->setActive($command->getActive());

        $this->campaignCategoryRepository->save($campaignCategory);
    }
}
