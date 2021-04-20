<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Status;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Bundle\UserBundle\Model\CustomerStatus;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Account\Infrastructure\Provider\AccountDetailsProviderInterface;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Infrastructure\Exception\LevelDowngradeModeNotSupportedException;
use OpenLoyalty\Component\Customer\Infrastructure\ExcludeDeliveryCostsProvider;
use OpenLoyalty\Component\Customer\Infrastructure\LevelDowngradeModeProvider;
use OpenLoyalty\Component\Customer\Infrastructure\Provider\CustomerDetailsProviderInterface;
use OpenLoyalty\Component\Customer\Infrastructure\TierAssignTypeProvider;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\LevelRepository;
use OpenLoyalty\Component\Level\Domain\Model\Reward;

/**
 * Class CustomerStatusProvider.
 */
class CustomerStatusProvider
{
    public const DEFAULT_CURRENCY = 'PLN';

    /**
     * @var Repository
     */
    private $accountDetailsRepository;

    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * @var CustomerDetailsProviderInterface
     */
    private $customerDetailsProvider;

    /**
     * @var TierAssignTypeProvider
     */
    private $tierAssignTypeProvider;

    /**
     * @var ExcludeDeliveryCostsProvider
     */
    private $excludeDeliveryCostProvider;

    /**
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * @var LevelDowngradeModeProvider
     */
    private $levelDowngradeModeProvider;

    /**
     * @var AccountDetailsProviderInterface
     */
    private $accountDetailsProvider;

    /**
     * CustomerStatusProvider constructor.
     *
     * @param Repository                       $accountDetailsRepository
     * @param LevelRepository                  $levelRepository
     * @param CustomerDetailsProviderInterface $customerDetailsProvider
     * @param TierAssignTypeProvider           $tierAssignTypeProvider
     * @param ExcludeDeliveryCostsProvider     $excludeDeliveryCostProvider
     * @param SettingsManager                  $settingsManager
     * @param LevelDowngradeModeProvider       $downgradeModeProvider
     * @param AccountDetailsProviderInterface  $accountDetailsProvider
     */
    public function __construct(
        Repository $accountDetailsRepository,
        LevelRepository $levelRepository,
        CustomerDetailsProviderInterface $customerDetailsProvider,
        TierAssignTypeProvider $tierAssignTypeProvider,
        ExcludeDeliveryCostsProvider $excludeDeliveryCostProvider,
        SettingsManager $settingsManager,
        LevelDowngradeModeProvider $downgradeModeProvider,
        AccountDetailsProviderInterface $accountDetailsProvider
    ) {
        $this->accountDetailsRepository = $accountDetailsRepository;
        $this->levelRepository = $levelRepository;
        $this->customerDetailsProvider = $customerDetailsProvider;
        $this->tierAssignTypeProvider = $tierAssignTypeProvider;
        $this->excludeDeliveryCostProvider = $excludeDeliveryCostProvider;
        $this->settingsManager = $settingsManager;
        $this->levelDowngradeModeProvider = $downgradeModeProvider;
        $this->accountDetailsProvider = $accountDetailsProvider;
    }

    /**
     * @param CustomerId $customerId
     *
     * @return CustomerStatus
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getStatus(CustomerId $customerId): CustomerStatus
    {
        $customerStatus = new CustomerStatus($customerId);
        $customerStatus->setCurrency($this->settingsCurrency());

        $customerDetails = $this->customerDetailsProvider->getCustomerDetailsByCustomerId($customerId);

        if (null === $customerDetails) {
            return $customerStatus;
        }

        $customerStatus->setFirstName($customerDetails->getFirstName());
        $customerStatus->setLastName($customerDetails->getLastName());

        $accountDetails = $this->accountDetailsProvider->getAccountDetailsByCustomerId($customerId);

        $nextLevel = null;

        /** @var Level $currentLevel */
        $currentLevel = $this->levelRepository->byId(new LevelId((string) $customerDetails->getLevelId()));

        if (null !== $currentLevel) {
            $conditionValue = $this->customerCurrentLevelConditionValue($customerDetails, $accountDetails);

            /** @var Level $nextLevel */
            $nextLevel = $this->levelRepository->findNextLevelByConditionValueWithTheBiggestReward(
                $conditionValue,
                $currentLevel->getConditionValue()
            );
        }

        if (null !== $accountDetails) {
            $this->applyAccountDetails($customerStatus, $customerDetails, $accountDetails);
        }

        if (null !== $currentLevel) {
            $customerStatus->setLevelName($currentLevel->getName());
            $customerStatus->setLevelPercent($this->rewardPercentageValue($currentLevel->getReward()));
            $customerStatus->setLevelConditionValue($currentLevel->getConditionValue());
        }

        if (null !== $nextLevel) {
            $customerStatus->setNextLevelName($nextLevel->getName());
            $customerStatus->setNextLevelPercent($this->rewardPercentageValue($nextLevel->getReward()));
            $customerStatus->setNextLevelConditionValue($nextLevel->getConditionValue());
        }

        if (null !== $currentLevel && null !== $nextLevel && $this->displayDowngradeModeXDaysStats()) {
            $pointsRequiredToRetainLevel = $customerStatus->getLevelConditionValue() - $customerStatus->getPointsSinceLastLevelRecalculation();

            if (0 > $pointsRequiredToRetainLevel) {
                $pointsRequiredToRetainLevel = 0.00;
            }

            $customerStatus->setPointsRequiredToRetainLevel($pointsRequiredToRetainLevel);
        }

        if (null !== $nextLevel && null !== $accountDetails) {
            $this->applyNextLevelRequirements($customerStatus, $customerDetails, $nextLevel, $accountDetails->getAvailableAmount());
        }

        if ($this->displayDowngradeModeXDaysStats()) {
            $days = $this->downgradeExpireDays($customerDetails);

            $customerStatus->setLevelWillExpireInDays($days);
        }

        $this->applyNextMonthExpirePoints($customerStatus, $accountDetails);
        $this->applyNextMonthExpirePointsDailyBreakdown($customerStatus, $accountDetails);

        return $customerStatus;
    }

    /**
     * @param CustomerStatus  $customerStatus
     * @param CustomerDetails $customerDetails
     * @param AccountDetails  $accountDetails
     */
    private function applyAccountDetails(
        CustomerStatus $customerStatus,
        CustomerDetails $customerDetails,
        AccountDetails $accountDetails
    ): void {
        $customerStatus->setPoints($accountDetails->getAvailableAmount());
        $customerStatus->setP2pPoints($accountDetails->getP2PAvailableAmount());
        $customerStatus->setTotalEarnedPoints($accountDetails->getEarnedAmount());
        $customerStatus->setUsedPoints($accountDetails->getUsedAmount());
        $customerStatus->setExpiredPoints($accountDetails->getExpiredAmount());
        $customerStatus->setLockedPoints($accountDetails->getLockedAmount());
        $customerStatus->setTransactionsAmount($customerDetails->getTransactionsAmount());
        $customerStatus->setTransactionsAmountWithoutDeliveryCosts($customerDetails->getTransactionsAmountWithoutDeliveryCosts());
        $customerStatus->setAverageTransactionsAmount(number_format($customerDetails->getAverageTransactionAmount(), 2, '.', ''));
        $customerStatus->setTransactionsCount($customerDetails->getTransactionsCount());

        if ($this->displayDowngradeModeXDaysStats()) {
            $startDate = $customerDetails->getLastLevelRecalculation() ?: $customerDetails->getCreatedAt();

            $customerStatus->setPointsSinceLastLevelRecalculation($accountDetails->getEarnedAmountSince($startDate));
        }
    }

    /**
     * @param CustomerStatus  $customerStatus
     * @param CustomerDetails $customerDetails
     * @param Level           $nextLevel
     * @param int             $currentPoints
     */
    private function applyNextLevelRequirements(
        CustomerStatus $customerStatus,
        CustomerDetails $customerDetails,
        Level $nextLevel,
        int $currentPoints
    ): void {
        $type = $this->tierAssignTypeProvider->getType();
        switch ($type) {
            case TierAssignTypeProvider::TYPE_POINTS:
                $customerStatus->setPointsToNextLevel($nextLevel->getConditionValue() - $currentPoints);

                return;
            case TierAssignTypeProvider::TYPE_TRANSACTIONS:
                $this->setCustomerTransactionAmountToNextLevel($customerStatus, $customerDetails, $nextLevel);

                return;
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param CustomerStatus $customerStatus
     * @param AccountDetails $accountDetails
     *
     * @throws \Exception
     */
    private function applyNextMonthExpirePoints(CustomerStatus $customerStatus, AccountDetails $accountDetails): void
    {
        $expiringPointsSum = 0;

        $addPointsTransfers = $accountDetails->getAllActiveAddPointsTransfers();
        foreach ($addPointsTransfers as $pointsTransfer) {
            if (null === $expiresAt = $pointsTransfer->getExpiresAt()) {
                continue;
            }

            $firstDayOfNextMonth = new \DateTime('first day of next month');
            $lastDayOfNextMonth = new \DateTime('last day of next month');

            if ($firstDayOfNextMonth > $expiresAt && $expiresAt < $lastDayOfNextMonth) {
                continue;
            }

            $expiringPointsSum += $pointsTransfer->getAvailableAmount();
        }

        $customerStatus->setPointsExpiringNextMonth($expiringPointsSum);
    }

    /**
     * @param CustomerStatus $customerStatus
     * @param AccountDetails $accountDetails
     *
     * @throws \Exception
     */
    private function applyNextMonthExpirePointsDailyBreakdown(CustomerStatus $customerStatus, AccountDetails $accountDetails): void
    {
        $expiringPointsDaily = [];

        $addPointsTransfers = $accountDetails->getAllActiveAddPointsTransfers();
        foreach ($addPointsTransfers as $pointsTransfer) {
            $expiresAt = $pointsTransfer->getExpiresAt();
            if (null === $expiresAt) {
                continue;
            }

            if ((new \DateTime('first day of this month')) >= $expiresAt) {
                continue;
            }

            if (!isset($expiringPointsDaily[$expiresAt->format('Y-m-d')])) {
                $expiringPointsDaily[$expiresAt->format('Y-m-d')] = 0;
            }
            $expiringPointsDaily[$expiresAt->format('Y-m-d')] += $pointsTransfer->getAvailableAmount();
        }
        ksort($expiringPointsDaily);

        $customerStatus->setPointsExpiringBreakdown($expiringPointsDaily);
    }

    /**
     * @param CustomerStatus  $customerStatus
     * @param CustomerDetails $customer
     * @param Level           $nextLevel
     */
    private function setCustomerTransactionAmountToNextLevel(
        CustomerStatus $customerStatus,
        CustomerDetails $customer,
        Level $nextLevel
    ): void {
        if ($this->excludeDeliveryCostProvider->areExcluded()) {
            $currentAmount = $customer->getTransactionsAmountWithoutDeliveryCosts() - $customer->getAmountExcludedForLevel();
            $customerStatus->setTransactionsAmountToNextLevelWithoutDeliveryCosts($nextLevel->getConditionValue() - $currentAmount);

            return;
        }

        $currentAmount = $customer->getTransactionsAmount() - $customer->getAmountExcludedForLevel();
        $customerStatus->setTransactionsAmountToNextLevel($nextLevel->getConditionValue() - $currentAmount);
    }

    /**
     * @return string
     */
    private function settingsCurrency(): string
    {
        $currency = $this->settingsManager->getSettingByKey('currency');

        if (null !== $currency) {
            return $currency->getValue();
        }

        return self::DEFAULT_CURRENCY;
    }

    /**
     * @param Reward $reward
     *
     * @return string
     */
    private function rewardPercentageValue(Reward $reward): string
    {
        return number_format($reward->getValue() * 100, 2).'%';
    }

    /**
     * @return bool
     */
    private function displayDowngradeModeXDaysStats(): bool
    {
        try {
            return
                $this->tierAssignTypeProvider->getType() == TierAssignTypeProvider::TYPE_POINTS &&
                $this->levelDowngradeModeProvider->getMode() === LevelDowngradeModeProvider::MODE_X_DAYS
            ;
        } catch (LevelDowngradeModeNotSupportedException $e) {
            return false;
        }
    }

    /**
     * @param $customerDetails
     *
     * @return int
     *
     * @throws \Exception
     */
    private function downgradeExpireDays(CustomerDetails $customerDetails): int
    {
        $date = $customerDetails->getLastLevelRecalculation() ?: $customerDetails->getCreatedAt();

        $nextDate = (clone $date)->modify(sprintf('+%u days', $this->levelDowngradeModeProvider->getDays()));
        $currentDate = new \DateTime();

        if ($nextDate < $currentDate) {
            return 0;
        }

        $diff = abs($nextDate->getTimestamp() - $currentDate->getTimestamp());

        return ceil($diff / 86400);
    }

    /**
     * @param AccountDetails  $accountDetails
     * @param CustomerDetails $customerDetails
     *
     * @return int
     */
    private function customerCurrentLevelConditionValue(
        CustomerDetails $customerDetails,
        ?AccountDetails $accountDetails
    ): int {
        $conditionValue = 0;

        $tierAssignType = $this->tierAssignTypeProvider->getType();
        if ($tierAssignType === TierAssignTypeProvider::TYPE_POINTS) {
            if (null !== $accountDetails) {
                $conditionValue = $accountDetails->getAvailableAmount();
            }
        } elseif ($tierAssignType === TierAssignTypeProvider::TYPE_TRANSACTIONS) {
            if ($this->excludeDeliveryCostProvider->areExcluded()) {
                $conditionValue = $customerDetails->getTransactionsAmountWithoutDeliveryCosts() - $customerDetails->getAmountExcludedForLevel();
            } else {
                $conditionValue = $customerDetails->getTransactionsAmount() - $customerDetails->getAmountExcludedForLevel();
            }
        }

        return $conditionValue;
    }
}
