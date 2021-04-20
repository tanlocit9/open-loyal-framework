<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Level\Infrastructure\Provider;

use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Account\Infrastructure\Provider\AccountDetailsProviderInterface;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Infrastructure\ExcludeDeliveryCostsProvider;
use OpenLoyalty\Component\Customer\Infrastructure\LevelDowngradeModeProvider;
use OpenLoyalty\Component\Customer\Infrastructure\Provider\CustomerDetailsProviderInterface;
use OpenLoyalty\Component\Customer\Infrastructure\TierAssignTypeProvider;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\LevelRepository;

/**
 * Class NextLevelProvider.
 */
class NextLevelProvider implements NextLevelProviderInterface
{
    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * @var CustomerDetailsProviderInterface
     */
    private $customerDetailsProvider;

    /**
     * @var AccountDetailsProviderInterface
     */
    private $accountDetailsProvider;

    /**
     * @var TierAssignTypeProvider
     */
    private $tierAssignTypeProvider;

    /**
     * @var ExcludeDeliveryCostsProvider
     */
    private $excludeDeliveryCostProvider;

    /**
     * @var LevelDowngradeModeProvider
     */
    private $levelDowngradeModeProvider;

    /**
     * NextLevelProvider constructor.
     *
     * @param LevelRepository                  $levelRepository
     * @param CustomerDetailsProviderInterface $customerDetailsProvider
     * @param AccountDetailsProviderInterface  $accountDetailsProvider
     * @param TierAssignTypeProvider           $tierAssignTypeProvider
     * @param ExcludeDeliveryCostsProvider     $excludeDeliveryCostProvider
     * @param LevelDowngradeModeProvider       $levelDowngradeModeProvider
     */
    public function __construct(
        LevelRepository $levelRepository,
        CustomerDetailsProviderInterface $customerDetailsProvider,
        AccountDetailsProviderInterface $accountDetailsProvider,
        TierAssignTypeProvider $tierAssignTypeProvider,
        ExcludeDeliveryCostsProvider $excludeDeliveryCostProvider,
        LevelDowngradeModeProvider $levelDowngradeModeProvider
    ) {
        $this->levelRepository = $levelRepository;
        $this->customerDetailsProvider = $customerDetailsProvider;
        $this->accountDetailsProvider = $accountDetailsProvider;
        $this->tierAssignTypeProvider = $tierAssignTypeProvider;
        $this->excludeDeliveryCostProvider = $excludeDeliveryCostProvider;
        $this->levelDowngradeModeProvider = $levelDowngradeModeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getNextLevelForCustomerId(CustomerId $customerId): ?Level
    {
        $accountDetails = $this->accountDetailsProvider->getAccountDetailsByCustomerId($customerId);

        if (null === $accountDetails) {
            return null;
        }

        $customerDetails = $this->customerDetailsProvider->getCustomerDetailsByCustomerId($customerId);

        if (null === $customerDetails || null === $customerDetails->getLevelId()) {
            return null;
        }

        $level = $this->levelRepository->byId(new LevelId((string) $customerDetails->getLevelId()));

        if (null === $level) {
            return null;
        }

        return $this->levelRepository->findPreviousLevelByConditionValueWithTheBiggestReward(
            $this->getConditionValue($accountDetails, $customerDetails),
            $level->getConditionValue()
        );
    }

    /**
     * @param AccountDetails  $accountDetails
     * @param CustomerDetails $customerDetails
     *
     * @return float
     */
    private function getConditionValue(AccountDetails $accountDetails, CustomerDetails $customerDetails): float
    {
        $tierAssignType = $this->tierAssignTypeProvider->getType();

        switch ($tierAssignType) {
            case TierAssignTypeProvider::TYPE_POINTS:
                return $this->pointsTierConditionValue($accountDetails, $customerDetails);
            case TierAssignTypeProvider::TYPE_TRANSACTIONS:
                return $this->transactionTierConditionValue($customerDetails);
        }

        return 0.0;
    }

    /**
     * @param AccountDetails  $accountDetails
     * @param CustomerDetails $customerDetails
     *
     * @return float
     */
    private function pointsTierConditionValue(AccountDetails $accountDetails, CustomerDetails $customerDetails): float
    {
        try {
            if (null === $accountDetails) {
                return 0.0;
            }

            if ($this->levelDowngradeModeProvider->getBase() === LevelDowngradeModeProvider::BASE_ACTIVE_POINTS) {
                return $accountDetails->getAvailableAmount();
            } elseif ($this->levelDowngradeModeProvider->getBase() === LevelDowngradeModeProvider::BASE_EARNED_POINTS_SINCE_LAST_LEVEL_CHANGE) {
                $startDate = $customerDetails->getLastLevelRecalculation() ?: $customerDetails->getCreatedAt();

                return $accountDetails->getEarnedAmountSince($startDate);
            } elseif ($this->levelDowngradeModeProvider->getBase() === LevelDowngradeModeProvider::BASE_EARNED_POINTS) {
                $startDate = $customerDetails->getLastLevelRecalculation() ?: $customerDetails->getCreatedAt();

                return $accountDetails->getEarnedAmountSince($startDate);
            }
        } catch (\Exception $e) {
            // nothing to do here
        }

        return 0.0;
    }

    /**
     * @param CustomerDetails $customerDetails
     *
     * @return float
     */
    private function transactionTierConditionValue(CustomerDetails $customerDetails): float
    {
        if ($this->excludeDeliveryCostProvider->areExcluded()) {
            return $customerDetails->getTransactionsAmountWithoutDeliveryCosts() - $customerDetails->getAmountExcludedForLevel();
        }

        return $customerDetails->getTransactionsAmount() - $customerDetails->getAmountExcludedForLevel();
    }
}
