<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Infrastructure\EventListener;

use OpenLoyalty\Component\Customer\Domain\SystemEvent\CampaignUsageWasChangedSystemEvent;

/**
 * Class CampaignRewardRedeemedSendEmailListener.
 */
interface CampaignRewardRedeemedSendEmailListenerInterface
{
    /**
     * @param CampaignUsageWasChangedSystemEvent $event
     */
    public function __invoke(CampaignUsageWasChangedSystemEvent $event): void;
}
