<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use OpenLoyalty\Component\EarningRule\Domain\Model\UsageSubject;

interface EarningRuleUsageRepository
{
    /**
     * @param EarningRuleUsageId $earningRuleUsageId
     *
     * @return mixed
     */
    public function byId(EarningRuleUsageId $earningRuleUsageId);

    /**
     * @return mixed
     */
    public function findAll();

    /**
     * @param EarningRule $earningRule
     *
     * @return mixed
     */
    public function save(EarningRule $earningRule);

    /**
     * @param EarningRule $earningRule
     *
     * @return mixed
     */
    public function remove(EarningRule $earningRule);

    /**
     * @param EarningRuleId $earningRuleId
     * @param UsageSubject  $subject
     *
     * @return int
     */
    public function countDailyUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int;

    /**
     * @param EarningRuleId $earningRuleId
     * @param UsageSubject  $subject
     *
     * @return int
     */
    public function countWeeklyUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int;

    /**
     * @param EarningRuleId $earningRuleId
     * @param UsageSubject  $subject
     *
     * @return int
     */
    public function countMonthlyUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int;

    /**
     * @param EarningRuleId $earningRuleId
     * @param UsageSubject  $subject
     *
     * @return int
     */
    public function countThreeMonthlyUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int;

    /**
     * @param EarningRuleId $earningRuleId
     * @param UsageSubject  $subject
     *
     * @return int
     */
    public function countSixMonthlyUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int;

    /**
     * @param EarningRuleId $earningRuleId
     * @param UsageSubject  $subject
     *
     * @return int
     */
    public function countYearUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int;

    /**
     * @param EarningRuleId $earningRuleId
     * @param UsageSubject  $subject
     *
     * @return int
     */
    public function countForeverUsage(EarningRuleId $earningRuleId, UsageSubject $subject): int;
}
