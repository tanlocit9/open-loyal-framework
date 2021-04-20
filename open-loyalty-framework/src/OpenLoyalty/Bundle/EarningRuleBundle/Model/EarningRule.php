<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Model;

use OpenLoyalty\Component\Core\Domain\Model\LabelMultiplier;
use OpenLoyalty\Component\EarningRule\Domain\CampaignId;
use OpenLoyalty\Component\EarningRule\Domain\EarningRule as BaseEarningRule;
use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Core\Domain\Model\SKU;
use OpenLoyalty\Component\EarningRule\Domain\PointsEarningRule;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;
use OpenLoyalty\Component\EarningRule\Domain\EarningRule as DomainEarningRule;

/**
 * Class EarningRule.
 *
 * @Assert\GroupSequenceProvider
 */
class EarningRule extends BaseEarningRule implements GroupSequenceProviderInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     * @Assert\Regex(pattern="/^([a-z0-9\_\.])+$/", groups={"custom_event"}, message="Allowed characters: 'a-z', '0-9', '_', '.'")
     */
    protected $eventName;

    /**
     * @var string
     */
    protected $rewardType;

    /**
     * @var int
     */
    protected $pointsAmount;

    /**
     * @var float
     */
    protected $pointValue;

    /**
     * @var SKU[]
     */
    protected $excludedSKUs = [];

    /**
     * @var LabelMultiplier[]
     */
    protected $labelMultipliers = [];

    /**
     * @var Label[]
     */
    protected $excludedLabels = [];

    /**
     * @var Label[]
     */
    protected $includedLabels = [];

    /**
     * @var string
     */
    protected $labelsInclusionType;

    /**
     * @var bool
     */
    protected $excludeDeliveryCost = true;

    /**
     * @var float
     */
    protected $minOrderValue = 0;

    /**
     * @var array
     */
    protected $skuIds;

    /**
     * @var array
     */
    protected $labels = [];

    /**
     * @var float
     */
    protected $multiplier;

    /**
     * @Assert\Valid()
     *
     * @var EarningRuleLimit
     */
    protected $limit;

    /**
     * @var CampaignId
     */
    protected $rewardCampaignId;

    /**
     * @var float|null
     */
    protected $latitude;

    /**
     * @var float|null
     */
    protected $longitude;

    /**
     * @var float|null
     */
    protected $radius;

    /**
     * @var string|null
     */
    protected $code;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
    }

    public function toArray()
    {
        $exSkus = array_map(
            function ($sku) {
                if (!$sku instanceof SKU) {
                    return;
                }

                return $sku->serialize();
            },
            $this->excludedSKUs
        );

        $exLabels = array_map(
            function ($label) {
                if (!$label instanceof Label) {
                    return;
                }

                return $label->serialize();
            },
            $this->excludedLabels
        );

        $inLabels = array_map(
            function ($label) {
                if (!$label instanceof Label) {
                    return;
                }

                return $label->serialize();
            },
            $this->includedLabels
        );

        $labels = array_map(
            function ($label) {
                if (!$label instanceof Label) {
                    return;
                }

                return $label->serialize();
            },
            $this->labels
        );

        $labelMultipliers = array_map(
            function ($labelMultiplier) {
                if (is_array($labelMultiplier)) {
                    return $labelMultiplier;
                } elseif ($labelMultiplier instanceof LabelMultiplier) {
                    return $labelMultiplier->serialize();
                }
            },
            $this->labelMultipliers
        );

        $data = [
            'name' => $this->getName(),
            'levels' => $this->levels,
            'segments' => $this->segments,
            'pos' => $this->pos,
            'active' => $this->isActive(),
            'startAt' => $this->startAt ? $this->startAt->getTimestamp() : null,
            'endAt' => $this->endAt ? $this->endAt->getTimestamp() : null,
            'allTimeActive' => $this->isAllTimeActive(),
            'eventName' => $this->getEventName(),
            'pointValue' => $this->pointValue,
            'pointsAmount' => $this->getPointsAmount(),
            'excludedSKUs' => $exSkus,
            'excludedLabels' => $exLabels,
            'includedLabels' => $inLabels,
            'labelsInclusionType' => $this->getLabelsInclusionType(),
            'excludeDeliveryCost' => $this->isExcludeDeliveryCost(),
            'minOrderValue' => $this->getMinOrderValue(),
            'skuIds' => $this->getSkuIds(),
            'multiplier' => $this->multiplier,
            'labels' => $labels,
            'labelMultipliers' => $labelMultipliers,
            'rewardType' => $this->rewardType,
            'rewardCampaignId' => (string) $this->rewardCampaignId,
            'lastExecutedRule' => $this->lastExecutedRule,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius' => $this->radius,
            'code' => $this->code,
            'description' => $this->description,
        ];
        if ($this->limit && ($this->type == self::TYPE_CUSTOM_EVENT || $this->type == self::TYPE_QRCODE || $this->type == self::TYPE_GEOLOCATION)) {
            $data['limit'] = [
                'period' => $this->limit->getPeriod(),
                'active' => $this->limit->isActive(),
                'limit' => $this->limit->getLimit(),
            ];
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @param string $eventName
     */
    public function setEventName($eventName)
    {
        $this->eventName = $eventName;
    }

    /**
     * @return int
     */
    public function getPointsAmount()
    {
        return $this->pointsAmount;
    }

    /**
     * @param int $pointsAmount
     */
    public function setPointsAmount($pointsAmount)
    {
        $this->pointsAmount = $pointsAmount;
    }

    /**
     * @return float
     */
    public function getPointValue()
    {
        return $this->pointValue;
    }

    /**
     * @param float $pointValue
     */
    public function setPointValue($pointValue)
    {
        $this->pointValue = $pointValue;
    }

    /**
     * @return \OpenLoyalty\Component\Core\Domain\Model\SKU[]
     */
    public function getExcludedSKUs()
    {
        return $this->excludedSKUs;
    }

    /**
     * @param \OpenLoyalty\Component\Core\Domain\Model\SKU[] $excludedSKUs
     */
    public function setExcludedSKUs($excludedSKUs)
    {
        $this->excludedSKUs = $excludedSKUs;
    }

    /**
     * @return \OpenLoyalty\Component\Core\Domain\Model\Label[]
     */
    public function getExcludedLabels()
    {
        return $this->excludedLabels;
    }

    /**
     * @param \OpenLoyalty\Component\Core\Domain\Model\Label[] $excludedLabels
     */
    public function setExcludedLabels($excludedLabels)
    {
        $this->excludedLabels = $excludedLabels;
    }

    /**
     * @return Label[]
     */
    public function getIncludedLabels(): array
    {
        return $this->includedLabels;
    }

    /**
     * @param Label[] $includedLabels
     */
    public function setIncludedLabels(array $includedLabels = [])
    {
        $this->includedLabels = $includedLabels;
    }

    /**
     * @return string|null
     */
    public function getLabelsInclusionType(): ?string
    {
        return $this->labelsInclusionType;
    }

    /**
     * @param string|null $labelsInclusionType
     */
    public function setLabelsInclusionType(string $labelsInclusionType = null)
    {
        $this->labelsInclusionType = $labelsInclusionType;
    }

    /**
     * @return bool
     */
    public function isExcludeDeliveryCost()
    {
        return $this->excludeDeliveryCost;
    }

    /**
     * @param bool $excludeDeliveryCost
     */
    public function setExcludeDeliveryCost($excludeDeliveryCost)
    {
        $this->excludeDeliveryCost = $excludeDeliveryCost;
    }

    /**
     * @return float
     */
    public function getMinOrderValue()
    {
        return $this->minOrderValue;
    }

    /**
     * @param float $minOrderValue
     */
    public function setMinOrderValue($minOrderValue)
    {
        $this->minOrderValue = $minOrderValue;
    }

    /**
     * @return array
     */
    public function getSkuIds()
    {
        return $this->skuIds;
    }

    /**
     * @param array $skuIds
     */
    public function setSkuIds($skuIds)
    {
        $this->skuIds = $skuIds;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @Assert\Callback(groups={"default", "custom_event"})
     */
    public function validateAllTimeActive(ExecutionContextInterface $context)
    {
        if (!$this->allTimeActive) {
            if (!$this->startAt) {
                $context->buildViolation('This value should not be blank.')->atPath('startAt')->addViolation();
            }
            if (!$this->endAt) {
                $context->buildViolation('This value should not be blank.')->atPath('endAt')->addViolation();
            }

            if ($this->startAt && $this->endAt) {
                if ($this->endAt <= $this->startAt) {
                    $context->buildViolation('This date must be later than Start at.')->atPath('endAt')->addViolation();
                }
            }
        }
    }

    /**
     * @return float
     */
    public function getMultiplier()
    {
        return $this->multiplier;
    }

    /**
     * @param float $multiplier
     */
    public function setMultiplier($multiplier)
    {
        $this->multiplier = $multiplier;
    }

    /**
     * @return \OpenLoyalty\Component\Core\Domain\Model\Label[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param \OpenLoyalty\Component\Core\Domain\Model\Label[] $labels
     */
    public function setLabels(array $labels)
    {
        $this->labels = $labels;
    }

    /**
     * @return LabelMultiplier[]
     */
    public function getLabelMultipliers(): array
    {
        return $this->labelMultipliers;
    }

    /**
     * @param LabelMultiplier[] $labelMultipliers
     */
    public function setLabelMultipliers(array $labelMultipliers): void
    {
        $this->labelMultipliers = $labelMultipliers;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupSequence()
    {
        $groups = ['default'];
        if ($this->type == DomainEarningRule::TYPE_CUSTOM_EVENT) {
            $groups[] = 'custom_event';
        }

        return $groups;
    }

    public static function createFromDomain(DomainEarningRule $rule)
    {
        $model = new self();
        foreach (DomainEarningRule::TYPE_MAP as $key => $val) {
            if ($rule instanceof $val) {
                $model->setType($key);

                break;
            }
        }

        return $model;
    }

    /**
     * @return string
     */
    public function getRewardType()
    {
        return $this->rewardType;
    }

    /**
     * @param string $rewardType
     */
    public function setRewardType($rewardType)
    {
        $this->rewardType = $rewardType;
    }

    /**
     * @return EarningRuleLimit
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param EarningRuleLimit $limit
     */
    public function setLimit(EarningRuleLimit $limit)
    {
        $this->limit = $limit;
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback()
     */
    public function validateSegmentsAndLevels(ExecutionContextInterface $context): void
    {
        if (count($this->levels) == 0 && count($this->segments) == 0) {
            $message = 'This collection should contain 1 element or more.';
            $context->buildViolation($message)->atPath('levels')->addViolation();
            $context->buildViolation($message)->atPath('segments')->addViolation();
        }
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback(groups={"default"})
     */
    public function validateLabelsInclusionType(ExecutionContextInterface $context): void
    {
        if ($this->type !== EarningRule::TYPE_POINTS) {
            return;
        }

        if (!$this->labelsInclusionType) {
            $context->buildViolation((new Assert\NotBlank())->message)->atPath('labelsInclusionType')
                ->addViolation();

            return;
        }

        if (!in_array($this->labelsInclusionType, [
            PointsEarningRule::LABELS_INCLUSION_TYPE_NONE,
            PointsEarningRule::LABELS_INCLUSION_TYPE_INCLUDE,
            PointsEarningRule::LABELS_INCLUSION_TYPE_EXCLUDE,
        ])) {
            $context->buildViolation('earning_rule.points_rule.labels_inclusion_type.choice_not_valid')->atPath('labelsInclusionType')
                ->addViolation();

            return;
        }
    }

    /**
     * @return null|CampaignId
     */
    public function getRewardCampaignId(): ?CampaignId
    {
        return $this->rewardCampaignId;
    }

    /**
     * @param CampaignId $rewardCampaignId
     */
    public function setRewardCampaignId(CampaignId $rewardCampaignId = null): void
    {
        $this->rewardCampaignId = $rewardCampaignId;
    }

    /**
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @param string|null $latitude
     */
    public function setLatitude(?string $latitude): void
    {
        $latitude = str_replace(',', '.', $latitude);
        $this->latitude = $latitude;
    }

    /**
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @param string|null $longitude
     */
    public function setLongitude(?string $longitude): void
    {
        $longitude = str_replace(',', '.', $longitude);
        $this->longitude = $longitude;
    }

    /**
     * @return float|null
     */
    public function getRadius(): ?float
    {
        return $this->radius;
    }

    /**
     * @param float|null $radius
     */
    public function setRadius(?float $radius): void
    {
        $this->radius = $radius;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }
}
