<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Repository;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;

/**
 * Interface CampaignRepositoryInterface.
 */
interface CampaignRepositoryInterface
{
    /**
     * @param CampaignId $campaignId
     *
     * @return Campaign|null
     */
    public function findOneById(CampaignId $campaignId): ?Campaign;
}
