<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Service;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use OpenLoyalty\Component\Campaign\Domain\Coupon\CouponCodeProvider;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBoughtRepository;

/**
 * Class EarningRuleCampaignProvider.
 */
class EarningRuleCampaignProvider implements EarningRuleCampaignProviderInterface
{
    /**
     * @var CampaignProvider
     */
    private $campaignProvider;

    /**
     * @var CampaignValidator
     */
    private $campaignValidator;

    /**
     * @var CampaignRepository
     */
    private $campaignRepository;

    /**
     * @var CouponCodeProvider
     */
    private $couponCodeProvider;

    /**
     * @var CampaignBoughtRepository
     */
    private $campaignBoughtRepository;

    /**
     * EarningRuleCampaignProvider constructor.
     *
     * @param CampaignProvider         $campaignProvider
     * @param CampaignValidator        $campaignValidator
     * @param CampaignRepository       $campaignRepository
     * @param CouponCodeProvider       $couponCodeProvider
     * @param CampaignBoughtRepository $campaignBoughtRepository
     */
    public function __construct(
        CampaignProvider $campaignProvider,
        CampaignValidator $campaignValidator,
        CampaignRepository $campaignRepository,
        CouponCodeProvider $couponCodeProvider,
        CampaignBoughtRepository $campaignBoughtRepository
    ) {
        $this->campaignProvider = $campaignProvider;
        $this->campaignValidator = $campaignValidator;
        $this->campaignRepository = $campaignRepository;
        $this->couponCodeProvider = $couponCodeProvider;
        $this->campaignBoughtRepository = $campaignBoughtRepository;
    }

    /**
     * @param string $campaignId
     *
     * @return Campaign
     */
    private function findCampaign(string $campaignId): Campaign
    {
        return $this->campaignRepository->byId(new CampaignId($campaignId));
    }

    /**
     * {@inheritdoc}
     */
    public function isActive(string $campaignId): bool
    {
        $campaign = $this->findCampaign($campaignId);
        if (!$campaign) {
            return false;
        }

        return $this->campaignValidator->isCampaignActive($campaign);
    }

    /**
     * {@inheritdoc}
     */
    public function isValidForCustomer(string $campaignId, string $customerId): bool
    {
        $campaign = $this->findCampaign($campaignId);
        if (!$campaign) {
            return false;
        }

        $customers = $this->campaignProvider->validForCustomers($campaign);

        return in_array($customerId, $customers, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewCouponCodeForDiscountPercentageCode(string $campaignId, float $transactionValue): Coupon
    {
        $campaign = $this->findCampaign($campaignId);

        $coupon = $this->couponCodeProvider->getCoupon($campaign, $transactionValue);

        return $coupon;
    }
}
