<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Service;

use OpenLoyalty\Component\Campaign\Domain\Provider\EarningRuleReturnCampaignBoughtProviderInterface;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBoughtRepository;
use OpenLoyalty\Component\EarningRule\Domain\CampaignId as EarningRuleCampaignId;
use OpenLoyalty\Component\EarningRule\Domain\Coupon as EarningRuleCoupon;
use OpenLoyalty\Component\EarningRule\Domain\Returns\Model\Campaign as EarningRuleCampaign;

/**
 * Class CampaignBoughtProvider.
 */
class EarningRuleReturnCampaignBoughtProvider implements EarningRuleReturnCampaignBoughtProviderInterface
{
    /**
     * @var CampaignBoughtRepository
     */
    private $campaignBoughtRepository;

    /**
     * CampaignBoughtProvider constructor.
     *
     * @param CampaignBoughtRepository $campaignBoughtRepository
     */
    public function __construct(CampaignBoughtRepository $campaignBoughtRepository)
    {
        $this->campaignBoughtRepository = $campaignBoughtRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTransactionAndCustomer(string $transactionId, string $customerId): array
    {
        $boughtCampaigns = $this->campaignBoughtRepository->findByTransactionIdAndCustomerId($transactionId, $customerId);
        $earningRuleCampaigns = [];

        foreach ($boughtCampaigns as $campaignBought) {
            $earningRuleCampaigns[] = new EarningRuleCampaign(
                new EarningRuleCampaignId((string) $campaignBought->getCampaignId()),
                new EarningRuleCoupon(
                    $campaignBought->getCoupon()->getId(),
                    $campaignBought->getCoupon()->getCode()
                ),
                $campaignBought->getCampaignType(),
                $campaignBought->getPurchasedAt(),
                $campaignBought->getStatus()
            );
        }

        return $earningRuleCampaigns;
    }
}
