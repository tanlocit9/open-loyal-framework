<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Infrastructure\Service;

use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;

/**
 * Class CampaignRewardRedeemedEmailSettingsProvider.
 */
interface CampaignRewardRedeemedEmailSenderInterface
{
    /**
     * @param CampaignBought $campaignBought
     */
    public function send(CampaignBought $campaignBought): void;
}
