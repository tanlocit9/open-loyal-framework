<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Infrastructure\Service;

use OpenLoyalty\Bundle\EmailBundle\DTO\EmailTemplateParameter;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;

/**
 * Class CampaignRewardRedeemedTemplateParameterCreator.
 */
interface CampaignRewardRedeemedTemplateParameterCreatorInterface
{
    /**
     * @param CampaignBought $campaignBought
     * @param string         $templateName
     *
     * @return EmailTemplateParameter
     *
     * @throws \Assert\AssertionFailedException
     */
    public function parameters(CampaignBought $campaignBought, string $templateName): EmailTemplateParameter;
}
