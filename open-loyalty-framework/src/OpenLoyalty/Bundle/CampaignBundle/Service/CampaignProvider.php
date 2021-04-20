<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Service;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use OpenLoyalty\Component\Campaign\Domain\CustomerId;
use OpenLoyalty\Component\Campaign\Domain\LevelId;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignUsage;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignUsageRepository;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CouponUsage;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CouponUsageRepository;
use OpenLoyalty\Component\Campaign\Domain\SegmentId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomersBelongingToOneLevel;
use OpenLoyalty\Component\Segment\Domain\ReadModel\SegmentedCustomers;

/**
 * Class CampaignProvider.
 */
class CampaignProvider
{
    /**
     * @var Repository
     */
    protected $segmentedCustomersRepository;

    /**
     * @var Repository
     */
    protected $customerBelongingToOneLevelRepository;

    /**
     * @var CouponUsageRepository
     */
    protected $couponUsageRepository;

    /**
     * @var CampaignValidator
     */
    protected $campaignValidator;

    /**
     * @var CampaignUsageRepository
     */
    private $campaignUsageRepository;

    /**
     * @var CampaignRepository
     */
    private $campaignRepository;

    /**
     * CampaignCustomersProvider constructor.
     *
     * @param Repository              $segmentedCustomersRepository
     * @param Repository              $customerBelongingToOneLevelRepository
     * @param CouponUsageRepository   $couponUsageRepository
     * @param CampaignValidator       $campaignValidator
     * @param CampaignUsageRepository $campaignUsageRepository
     * @param CampaignRepository      $campaignRepository
     */
    public function __construct(
        Repository $segmentedCustomersRepository,
        Repository $customerBelongingToOneLevelRepository,
        CouponUsageRepository $couponUsageRepository,
        CampaignValidator $campaignValidator,
        CampaignUsageRepository $campaignUsageRepository,
        CampaignRepository $campaignRepository
    ) {
        $this->segmentedCustomersRepository = $segmentedCustomersRepository;
        $this->customerBelongingToOneLevelRepository = $customerBelongingToOneLevelRepository;
        $this->couponUsageRepository = $couponUsageRepository;
        $this->campaignValidator = $campaignValidator;
        $this->campaignUsageRepository = $campaignUsageRepository;
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * @param CustomerDetails $customer
     *
     * @return null|Campaign
     */
    public function getCashbackForCustomer(CustomerDetails $customer): ?Campaign
    {
        $customerSegments = $this->segmentedCustomersRepository->findBy(
            ['customerId' => (string) $customer->getCustomerId()]
        );
        $segments = array_map(function (SegmentedCustomers $segmentedCustomers) {
            return new SegmentId((string) $segmentedCustomers->getSegmentId());
        }, $customerSegments);

        $availableCampaigns = $this->campaignRepository->getActiveCashbackCampaignsForLevelAndSegment(
            $segments,
            new LevelId((string) $customer->getLevelId())
        );

        if (!$availableCampaigns) {
            return null;
        }

        /** @var Campaign $best */
        $best = null;

        /** @var Campaign $campaign */
        foreach ($availableCampaigns as $campaign) {
            if (null == $best || $campaign->getPointValue() > $best->getPointValue()) {
                $best = $campaign;
            }
        }

        return $best;
    }

    /**
     * @param Campaign $campaign
     *
     * @return array
     */
    public function visibleForCustomers(Campaign $campaign): array
    {
        if (!$this->campaignValidator->isCampaignVisible($campaign)) {
            return [];
        }

        return $this->validForCustomers($campaign);
    }

    /**
     * @param Campaign $campaign
     *
     * @return array
     */
    public function validForCustomers(Campaign $campaign): array
    {
        $customers = [];

        foreach ($campaign->getSegments() as $segmentId) {
            $segmented = $this->segmentedCustomersRepository->findBy(['segmentId' => (string) $segmentId]);
            /** @var SegmentedCustomers $segm */
            foreach ($segmented as $segm) {
                $customers[(string) $segm->getCustomerId()] = (string) $segm->getCustomerId();
            }
        }

        foreach ($campaign->getLevels() as $levelId) {
            $cst = $this->customerBelongingToOneLevelRepository->findBy(['levelId' => (string) $levelId]);
            /** @var CustomersBelongingToOneLevel $c */
            foreach ($cst as $c) {
                foreach ($c->getCustomers() as $cust) {
                    $customers[$cust['customerId']] = $cust['customerId'];
                }
            }
        }

        return $customers;
    }

    /**
     * @param Campaign $campaign
     *
     * @return array
     */
    public function getAllCoupons(Campaign $campaign): array
    {
        return array_map(function (Coupon $coupon) {
            return $coupon->getCode();
        }, $campaign->getCoupons());
    }

    /**
     * @param Campaign $campaign
     *
     * @return array
     */
    public function getUsedCoupons(Campaign $campaign): array
    {
        return array_map(function (CouponUsage $couponUsage) {
            return $couponUsage->getCoupon()->getCode();
        }, $this->couponUsageRepository->findByCampaign($campaign->getCampaignId()));
    }

    /**
     * @param Campaign $campaign
     *
     * @return array
     */
    public function getFreeCoupons(Campaign $campaign): array
    {
        return array_diff($this->getAllCoupons($campaign), $this->getUsedCoupons($campaign));
    }

    /**
     * @param Campaign $campaign
     *
     * @return int
     */
    public function getUsageLeft(Campaign $campaign): int
    {
        $used = $this->couponUsageRepository->countUsageForCampaign($campaign->getCampaignId());

        $usageLeft = $campaign->getLimit() - $used;
        if ($usageLeft < 0) {
            $usageLeft = 0;
        }
        $freeCoupons = $this->getCouponsUsageLeftCount($campaign);

        if ($campaign->isUnlimited()) {
            return $freeCoupons;
        }

        return min($freeCoupons, $usageLeft);
    }

    /**
     * @param Campaign   $campaign
     * @param CustomerId $customerId
     *
     * @return int
     */
    public function getUsageLeftForCustomer(Campaign $campaign, CustomerId $customerId): int
    {
        $freeCoupons = $this->getCouponsUsageLeftCount($campaign);
        if (!$campaign->isSingleCoupon()) {
            $usageForCustomer = $this->couponUsageRepository->countUsageForCampaignAndCustomer(
                $campaign->getCampaignId(),
                $customerId
            );
        } else {
            $campaignCoupon = $this->getAllCoupons($campaign);
            $usageForCustomer = $this->couponUsageRepository->countUsageForCampaignAndCustomerAndCode(
                $campaign->getCampaignId(),
                $customerId,
                reset($campaignCoupon)
            );
        }
        $usageLeftForCustomer = $campaign->getLimitPerUser() - $usageForCustomer;

        if ($usageLeftForCustomer < 0) {
            $usageLeftForCustomer = 0;
        }

        if ($campaign->isUnlimited()) {
            return $freeCoupons;
        }

        return min($freeCoupons, $usageLeftForCustomer);
    }

    /**
     * @param Campaign $campaign
     *
     * @return int
     */
    protected function getCouponsUsageLeftCount($campaign)
    {
        if (!$campaign->isSingleCoupon()) {
            $freeCoupons = count($this->getFreeCoupons($campaign));
        } else {
            $usages = 0;
            $usagesRepo = $this->campaignUsageRepository->find($campaign->getCampaignId());
            if ($usagesRepo instanceof CampaignUsage) {
                $usages = $usagesRepo->getCampaignUsage();
            }
            if ($campaign->isUnlimited()) {
                $freeCoupons = PHP_INT_MAX;
            } else {
                $freeCoupons = ($campaign->getLimit() - $usages) < 0 ? 0 : $campaign->getLimit() - $usages;
            }
        }

        return $freeCoupons;
    }

    /**
     * @param Campaign $campaign
     * @param array    $coupons
     *
     * @return array
     */
    public function getDeletedAndUsedCoupons(Campaign $campaign, array $coupons): array
    {
        $removedCoupons = array_filter($campaign->getCoupons(), function (Coupon $campaignCoupon) use ($coupons): bool {
            foreach ($coupons as $formCoupon) {
                if ($formCoupon->getCode() === $campaignCoupon->getCode()) {
                    return false;
                }
            }

            return true;
        });

        $usedCoupons = $this->couponUsageRepository->findByCampaign($campaign->getCampaignId());

        $deletedAndUsed = array_filter($removedCoupons, function (Coupon $removedCoupon) use ($usedCoupons): bool {
            foreach ($usedCoupons as $usedCoupon) {
                if ($removedCoupon->getCode() === $usedCoupon->getCoupon()->getCode()) {
                    return true;
                }
            }

            return false;
        });

        return $deletedAndUsed;
    }
}
