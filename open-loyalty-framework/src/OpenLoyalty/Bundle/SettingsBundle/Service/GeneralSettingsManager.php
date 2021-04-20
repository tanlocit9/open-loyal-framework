<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Service;

use OpenLoyalty\Bundle\SettingsBundle\Entity\SettingsEntry;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Customer\Domain\Model\AccountActivationMethod;

/**
 * Class GeneralSettingsManager.
 */
class GeneralSettingsManager extends DoctrineSettingsManager implements GeneralSettingsManagerInterface
{
    const DEFAULT_POINTS_DURATION_VALIDITY_DAYS = 90;
    const DEFAULT_CURRENCY = 'PLN';

    /**
     * @var null
     */
    private $programName = null;

    /**
     * @return int
     */
    public function getPointsDaysActive(): ?int
    {
        $numberOfDays = self::DEFAULT_POINTS_DURATION_VALIDITY_DAYS;

        $pointsDaysExpiryAfter = $this->getSettingByKey('pointsDaysExpiryAfter');
        if (!$pointsDaysExpiryAfter) {
            return $numberOfDays;
        }

        switch ($pointsDaysExpiryAfter->getValue()) {
            case AddPointsTransfer::TYPE_ALL_TIME_ACTIVE:
                $numberOfDays = null;
                break;
            case AddPointsTransfer::TYPE_AT_MONTH_END:
                $today = $this->getDateTime();
                $today->setTime(0, 0);
                $lastDayOfThisMonth = $this->getDateTime('last day of this month');
                $numberOfDays = (int) $lastDayOfThisMonth->diff($today)->format('%a');
                break;
            case AddPointsTransfer::TYPE_AT_YEAR_END:
                $today = $this->getDateTime();
                $today->setTime(0, 0);
                $lastDayOfThisYear = $this->getDateTime('last day of december this year');
                $numberOfDays = (int) $lastDayOfThisYear->diff($today)->format('%a');
                break;
            case AddPointsTransfer::TYPE_AFTER_X_DAYS:
                $pointsDaysActiveCount = $this->getSettingByKey('pointsDaysActiveCount');
                if ($pointsDaysActiveCount instanceof SettingsEntry && $pointsDaysActiveCount->getValue()) {
                    $numberOfDays = $pointsDaysActiveCount->getValue();
                }
                break;
        }

        return $numberOfDays;
    }

    /**
     * {@inheritdoc}
     */
    public function getPointsDaysLocked(): ?int
    {
        $allTimeNotLocked = $this->getSettingByKey('allTimeNotLocked');
        if ($allTimeNotLocked && $allTimeNotLocked->getValue()) {
            return null;
        }
        $pointsDaysLocked = $this->getSettingByKey('pointsDaysLocked');

        return $pointsDaysLocked ? $pointsDaysLocked->getValue() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrency(): string
    {
        return $this->getSettingByKey('currency')->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimezone(): string
    {
        return $this->getSettingByKey('timezone')->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramName(): string
    {
        if (null === $this->programName) {
            $this->programName = $this->getSettingByKey('programName')->getValue();
        }

        return $this->programName;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramUrl(): ?string
    {
        return $this->getSettingByKey('programUrl')->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionsUrl(): ?string
    {
        return $this->getSettingByKey('programUrl')->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function FAQUrl(): ?string
    {
        return $this->getSettingByKey('programFaqUrl')->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getPointsSingular(): string
    {
        return $this->getSettingByKey('programPointsSingular')->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getPointsPlural(): string
    {
        return $this->getSettingByKey('programPointsPlural')->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getHelpEmail(): ?string
    {
        return $this->getSettingByKey('helpEmailAddress')->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getPushySecretKey(): ?string
    {
        return $this->getSettingByKey('pushySecretKey')->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function isAllTimeActive(): ?bool
    {
        return $this->getSettingByKey('allTimeActive')->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function isReturnAvailable(): bool
    {
        return (bool) $this->getSettingByKey('returns')->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function isDeliveryCostExcluded(): bool
    {
        return (bool) $this->getSettingByKey('excludeDeliveryCostsFromTierAssignment')->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getAccountActivationMethod(): string
    {
        return $this->getSettingByKey('accountActivationMethod')->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function isSmsAccountActivationMethod(): bool
    {
        return $this->getAccountActivationMethod() === AccountActivationMethod::METHOD_SMS;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmailAccountActivationMethod(): bool
    {
        return $this->getAccountActivationMethod() === AccountActivationMethod::METHOD_EMAIL;
    }

    /**
     * @param null|string $time
     *
     * @return \DateTime
     */
    protected function getDateTime(?string $time = null): \DateTime
    {
        return new \DateTime($time);
    }
}
