<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Infrastructure\Provider;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;

/**
 * Class RewardCampaignProvider.
 */
interface RewardCampaignProviderInterface
{
    /**
     * @param CampaignId $campaignId
     *
     * @return Campaign
     *
     * @throws \Assert\AssertionFailedException
     */
    public function findById(CampaignId $campaignId): Campaign;
}
