<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain;

/**
 * Interface CampaignCategoryRepository.
 */
interface CampaignCategoryRepository
{
    /**
     * @param CampaignCategoryId $campaignCategoryId
     *
     * @return CampaignCategory
     */
    public function byId(CampaignCategoryId $campaignCategoryId): CampaignCategory;

    /**
     * @return CampaignCategory[]
     */
    public function findAll(): array;

    /**
     * @return int
     */
    public function countTotal(): int;

    /**
     * @param CampaignCategory $campaignCategory
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(CampaignCategory $campaignCategory): void;

    /**
     * @param CampaignCategory $campaignCategory
     */
    public function remove(CampaignCategory $campaignCategory): void;

    /**
     * @param array       $params
     * @param int         $page
     * @param int         $perPage
     * @param string|null $sortField
     * @param string      $direction
     *
     * @return CampaignCategory[]
     */
    public function findByParametersPaginated(
        array $params,
        int $page = 1,
        int $perPage = 10,
        ?string $sortField = null,
        string $direction = 'ASC'
    ): array;

    /**
     * @param array $params
     *
     * @return int
     */
    public function countFindByParameters(array $params): int;
}
