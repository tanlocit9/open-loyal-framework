<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Service;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Bundle\CampaignBundle\Exception\CampaignLimitExceededException;
use OpenLoyalty\Bundle\CampaignBundle\Exception\CampaignLimitPerCustomerExceededException;
use OpenLoyalty\Bundle\CampaignBundle\Exception\NoCouponsLeftException;
use OpenLoyalty\Bundle\CampaignBundle\Exception\NotAllowedException;
use OpenLoyalty\Bundle\CampaignBundle\Exception\NotEnoughPointsException;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CustomerId;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CouponUsageRepository;
use OpenLoyalty\Component\Customer\Domain\Model\Status;

/**
 * Class CampaignValidator.
 */
class CampaignValidator
{
    /**
     * @var CouponUsageRepository
     */
    protected $couponUsageRepository;

    /**
     * @var Repository
     */
    protected $accountDetailsRepository;

    /**
     * @var SettingsManager
     */
    protected $settingsManager;

    /**
     * CampaignValidator constructor.
     *
     * @param CouponUsageRepository $couponUsageRepository
     * @param Repository            $accountDetailsRepository
     * @param SettingsManager       $settingsManager
     */
    public function __construct(
        CouponUsageRepository $couponUsageRepository,
        Repository $accountDetailsRepository,
        SettingsManager $settingsManager
    ) {
        $this->couponUsageRepository = $couponUsageRepository;
        $this->accountDetailsRepository = $accountDetailsRepository;
        $this->settingsManager = $settingsManager;
    }

    /**
     * @param Campaign   $campaign
     * @param CustomerId $customerId
     * @param int        $quantity
     *
     * @throws CampaignLimitExceededException
     * @throws CampaignLimitPerCustomerExceededException
     * @throws NoCouponsLeftException
     */
    public function validateCampaignLimits(Campaign $campaign, CustomerId $customerId, int $quantity = 1): void
    {
        if ($campaign->isPercentageDiscountCode() || $campaign->isCustomReward()) {
            return;
        }
        if ($campaign->isCashback()) {
            $quantity = 1;
        }
        $countUsageForCampaign = $this->couponUsageRepository->countUsageForCampaign($campaign->getCampaignId());
        $neededUsageForCampaign = $countUsageForCampaign + $quantity;

        if ($campaign->isUnlimited()) {
            if (!$campaign->isSingleCoupon() && $neededUsageForCampaign > count($campaign->getCoupons())) {
                throw new NoCouponsLeftException();
            }
        } else {
            if ($neededUsageForCampaign > $campaign->getLimit()) {
                throw new CampaignLimitExceededException();
            }
            $countUsageForCampaignAndCustomer = $this->couponUsageRepository->countUsageForCampaignAndCustomer(
                $campaign->getCampaignId(),
                $customerId
            );
            $neededUsageForCampaignAndCustomer = $countUsageForCampaignAndCustomer + $quantity;
            if ($neededUsageForCampaignAndCustomer > $campaign->getLimitPerUser()) {
                throw new CampaignLimitPerCustomerExceededException();
            }
        }
    }

    /**
     * @param Status $customerStatus
     *
     * @throws NotAllowedException
     */
    public function checkIfCustomerStatusIsAllowed(Status $customerStatus): void
    {
        if (null === $customerStatus || !in_array($customerStatus->getType(), $this->getCustomerSpendingStatuses())) {
            throw new NotAllowedException();
        }
    }

    /**
     * @param Campaign   $campaign
     * @param CustomerId $customerId
     * @param int        $quantity
     *
     * @throws NotEnoughPointsException
     */
    public function checkIfCustomerHasEnoughPoints(Campaign $campaign, CustomerId $customerId, int $quantity = 1): void
    {
        if ($campaign->isCashback() || $campaign->isPercentageDiscountCode()) {
            $quantity = 1;
        }
        $accounts = $this->accountDetailsRepository->findBy(['customerId' => $customerId->__toString()]);
        if (count($accounts) == 0) {
            throw new NotEnoughPointsException();
        }
        /** @var AccountDetails $account */
        $account = reset($accounts);
        $availableAmount = $account->getAvailableAmount();
        if ($availableAmount < ($campaign->getCostInPoints() * $quantity)) {
            throw new NotEnoughPointsException();
        }
    }

    /**
     * @param float      $points
     * @param CustomerId $customerId
     *
     * @return bool
     *
     * @throws NotEnoughPointsException
     */
    public function hasCustomerEnoughPointsForCashback($points, CustomerId $customerId)
    {
        $accounts = $this->accountDetailsRepository->findBy(['customerId' => $customerId->__toString()]);
        if (count($accounts) == 0) {
            throw new NotEnoughPointsException();
        }
        /** @var AccountDetails $account */
        $account = reset($accounts);
        if ($account->getAvailableAmount() < $points) {
            throw new NotEnoughPointsException();
        }

        return true;
    }

    public function isCampaignActive(Campaign $campaign)
    {
        if (!$campaign->isActive()) {
            return false;
        }

        $campaignActivity = $campaign->getCampaignActivity();
        if ($campaignActivity->isAllTimeActive()) {
            return true;
        }
        $now = new \DateTime();
        if ($campaignActivity->getActiveFrom() <= $now && $now <= $campaignActivity->getActiveTo()) {
            return true;
        }

        return false;
    }

    public function isCampaignVisible(Campaign $campaign)
    {
        if ($campaign->isCashback() || $campaign->isPercentageDiscountCode()) {
            return false;
        }

        $campaignVisibility = $campaign->getCampaignVisibility();
        if ($campaignVisibility->isAllTimeVisible()) {
            return true;
        }
        $now = new \DateTime();
        if ($campaignVisibility->getVisibleFrom() <= $now && $now <= $campaignVisibility->getVisibleTo()) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getCustomerSpendingStatuses()
    {
        $customerStatusesSpending = $this->settingsManager->getSettingByKey('customerStatusesSpending');
        if ($customerStatusesSpending) {
            return $customerStatusesSpending->getValue();
        }

        return [];
    }
}
