<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Service;

use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\EarningRule\Domain\CustomEventEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleGeo;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleLimit;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleQrcode;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleRepository;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleUsageRepository;
use OpenLoyalty\Component\EarningRule\Domain\Model\UsageSubject;
use OpenLoyalty\Component\Account\Infrastructure\EarningRuleLimitValidator;
use OpenLoyalty\Component\Account\Infrastructure\Exception\EarningRuleLimitExceededException;

/**
 * Class OloyEarningRuleValidator.
 */
class OloyEarningRuleValidator implements EarningRuleLimitValidator
{
    /**
     * @var EarningRuleUsageRepository
     */
    protected $earningRuleUsageRepository;

    /**
     * @var EarningRuleRepository
     */
    protected $earningRuleRepository;

    /**
     * OloyEarningRuleValidator constructor.
     *
     * @param EarningRuleUsageRepository $earningRuleUsageRepository
     * @param EarningRuleRepository      $earningRuleRepository
     */
    public function __construct(
        EarningRuleUsageRepository $earningRuleUsageRepository,
        EarningRuleRepository $earningRuleRepository
    ) {
        $this->earningRuleUsageRepository = $earningRuleUsageRepository;
        $this->earningRuleRepository = $earningRuleRepository;
    }

    /**
     * @param $earningRuleId
     * @param CustomerId $customerId
     *
     * @throws EarningRuleLimitExceededException
     */
    public function validate($earningRuleId, CustomerId $customerId)
    {
        $repo = $this->earningRuleUsageRepository;
        $earningRuleId = new EarningRuleId($earningRuleId);
        /** @var CustomEventEarningRule $earningRule */
        $earningRule = $this->earningRuleRepository->byId($earningRuleId);
        if (!($earningRule instanceof CustomEventEarningRule || $earningRule instanceof EarningRuleQrcode || $earningRule instanceof EarningRuleGeo)) {
            return;
        }
        $limit = $earningRule->getLimit();
        if (!$limit || !$limit->isActive()) {
            return;
        }
        $subject = new UsageSubject($customerId->__toString());

        switch ($limit->getPeriod()) {
            case EarningRuleLimit::PERIOD_DAY:
                $usage = $repo->countDailyUsage($earningRuleId, $subject);
                break;
            case EarningRuleLimit::PERIOD_WEEK:
                $usage = $repo->countWeeklyUsage($earningRuleId, $subject);
                break;
            case EarningRuleLimit::PERIOD_MONTH:
                $usage = $repo->countMonthlyUsage($earningRuleId, $subject);
                break;
            case EarningRuleLimit::PERIOD_3_MONTHS:
                $usage = $repo->countThreeMonthlyUsage($earningRuleId, $subject);
                break;
            case EarningRuleLimit::PERIOD_6_MONTHS:
                $usage = $repo->countSixMonthlyUsage($earningRuleId, $subject);
                break;
            case EarningRuleLimit::PERIOD_YEAR:
                $usage = $repo->countYearUsage($earningRuleId, $subject);
                break;
            case EarningRuleLimit::PERIOD_FOREVER:
                $usage = $repo->countForeverUsage($earningRuleId, $subject);
                break;
            default:
                $usage = null;
        }
        if (null !== $usage && $usage >= $limit->getLimit()) {
            throw new EarningRuleLimitExceededException();
        }
    }
}
