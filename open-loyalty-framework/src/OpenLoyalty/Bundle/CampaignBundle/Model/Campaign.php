<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Model;

use OpenLoyalty\Component\Campaign\Domain\Campaign as BaseCampaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignTranslation;
use OpenLoyalty\Component\Core\Domain\Model\Label;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Campaign.
 */
class Campaign extends BaseCampaign
{
    /**
     * Campaign constructor.
     */
    public function __construct()
    {
        parent::__construct(null, []);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $this->mergeNewTranslations();

        $labels = array_map(
            function ($label) {
                if (!$label instanceof Label) {
                    return;
                }

                return $label->serialize();
            },
            $this->labels
        );

        $translations = array_map(
            function (CampaignTranslation $campaign): array {
                return [
                    'name' => $campaign->getName(),
                    'shortDescription' => $campaign->getShortDescription(),
                    'conditionsDescription' => $campaign->getConditionsDescription(),
                    'usageInstruction' => $campaign->getUsageInstruction(),
                    'brandDescription' => $campaign->getBrandDescription(),
                    'brandName' => $campaign->getBrandName(),
                ];
            },
            $this->getTranslations()->toArray()
        );

        return [
            'reward' => $this->reward,
            'moreInformationLink' => $this->moreInformationLink,
            'pushNotificationText' => $this->pushNotificationText,
            'active' => $this->active,
            'costInPoints' => $this->costInPoints,
            'pointValue' => $this->pointValue,
            'levels' => $this->levels,
            'segments' => $this->segments,
            'unlimited' => $this->unlimited,
            'singleCoupon' => $this->singleCoupon,
            'limit' => $this->limit,
            'limitPerUser' => $this->limitPerUser,
            'coupons' => $this->coupons,
            'campaignActivity' => $this->campaignActivity ? $this->campaignActivity->toArray() : null,
            'campaignVisibility' => $this->campaignVisibility ? $this->campaignVisibility->toArray() : null,
            'rewardValue' => $this->rewardValue,
            'tax' => $this->tax,
            'taxPriceValue' => $this->taxPriceValue,
            'labels' => $labels,
            'translations' => $translations,
            'daysInactive' => $this->daysInactive,
            'daysValid' => $this->daysValid,
            'transactionPercentageValue' => $this->transactionPercentageValue,
            'featured' => $this->featured,
            'public' => $this->public,
            'categories' => $this->categories,
            'connectType' => $this->connectType,
            'earningRuleId' => $this->earningRuleId,
            'fulfillmentTracking' => $this->fulfillmentTracking,
        ];
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback()
     */
    public function validateLimit(ExecutionContextInterface $context)
    {
        if ($this->unlimited) {
            return;
        }

        if ($this->reward === self::REWARD_TYPE_CASHBACK || $this->reward === self::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE) {
            return;
        }

        if ($this->reward === self::REWARD_TYPE_CUSTOM_CAMPAIGN_CODE) {
            return;
        }

        if (!$this->limit) {
            $context->buildViolation((new NotBlank())->message)->atPath('limit')->addViolation();
        }
        if (!$this->limitPerUser) {
            $context->buildViolation((new NotBlank())->message)->atPath('limitPerUser')->addViolation();
        }
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback()
     */
    public function validateSegmentsAndLevels(ExecutionContextInterface $context)
    {
        if (count($this->levels) == 0 && count($this->segments) == 0) {
            $message = 'This collection should contain 1 element or more.';
            $context->buildViolation($message)->atPath('levels')->addViolation();
            $context->buildViolation($message)->atPath('segments')->addViolation();
        }
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback()
     */
    public function validateCoupons(ExecutionContextInterface $context)
    {
        if ($this->reward === self::REWARD_TYPE_CASHBACK || $this->reward === self::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE) {
            return;
        }

        if ($this->reward === self::REWARD_TYPE_CUSTOM_CAMPAIGN_CODE) {
            return;
        }

        if (count($this->coupons) == 0) {
            $message = 'This collection should contain 1 element or more.';
            $context->buildViolation($message)->atPath('coupons')->addViolation();
        }
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback()
     */
    public function validateTax(ExecutionContextInterface $context)
    {
        if (!empty($this->tax)) {
            if (!filter_var($this->tax, FILTER_VALIDATE_INT)) {
                $context->buildViolation('This value should be of type integer.')->atPath('tax')->addViolation();
            }
        }
    }
}
