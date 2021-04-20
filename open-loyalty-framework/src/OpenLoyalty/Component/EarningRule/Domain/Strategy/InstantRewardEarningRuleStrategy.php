<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Strategy;

use OpenLoyalty\Bundle\CampaignBundle\Service\EarningRuleCampaignProviderInterface;
use OpenLoyalty\Component\EarningRule\Domain\Algorithm\RuleEvaluationContextInterface;
use OpenLoyalty\Component\EarningRule\Domain\EarningRule;

/**
 * Class InstantRewardEarningRuleStrategy.
 */
class InstantRewardEarningRuleStrategy implements EarningRuleStrategy
{
    /**
     * @var EarningRuleCampaignProviderInterface
     */
    private $campaignProvider;

    /**
     * InstantRewardEarningRuleStrategy constructor.
     *
     * @param EarningRuleCampaignProviderInterface $campaignProvider
     */
    public function __construct(EarningRuleCampaignProviderInterface $campaignProvider)
    {
        $this->campaignProvider = $campaignProvider;
    }

    /**
     * @param RuleEvaluationContextInterface $context
     * @param EarningRule                    $rule
     *
     * @return bool
     */
    public function isApplicable(RuleEvaluationContextInterface $context, EarningRule $rule): bool
    {
        $campaignId = $rule->getRewardCampaignId()->__toString();

        return $this->isActive($campaignId)
            && $this->isValidForCustomer($campaignId, $context->getCustomerId());
    }

    /**
     * @param string $campaignId
     *
     * @return bool
     */
    private function isActive(string $campaignId): bool
    {
        return $this->campaignProvider->isActive($campaignId);
    }

    /**
     * @param string $campaignId
     * @param string $customerId
     *
     * @return bool
     */
    private function isValidForCustomer(string $campaignId, string $customerId): bool
    {
        return $this->campaignProvider->isValidForCustomer($campaignId, $customerId);
    }
}
