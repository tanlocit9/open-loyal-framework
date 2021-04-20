<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Core\Domain\Model\SKU;
use Assert\Assertion as Assert;
use OpenLoyalty\Component\EarningRule\Domain\Stoppable\StoppableInterface;

/**
 * Class PointsEarningRule.
 */
class PointsEarningRule extends EarningRule implements StoppableInterface
{
    const LABELS_INCLUSION_TYPE_NONE = 'none_labels';
    const LABELS_INCLUSION_TYPE_INCLUDE = 'include_labels';
    const LABELS_INCLUSION_TYPE_EXCLUDE = 'exclude_labels';

    /**
     * @var float
     */
    protected $pointValue;

    /**
     * @var SKU[]
     */
    protected $excludedSKUs = [];

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
    protected $labelsInclusionType = self::LABELS_INCLUSION_TYPE_NONE;

    /**
     * @var bool
     */
    protected $excludeDeliveryCost = true;

    /**
     * @var float
     */
    protected $minOrderValue = 0;

    public function setFromArray(array $earningRuleData = [])
    {
        parent::setFromArray($earningRuleData);

        if (isset($earningRuleData['pointValue'])) {
            $this->pointValue = $earningRuleData['pointValue'];
        }
        if (isset($earningRuleData['excludedSKUs'])) {
            $skus = [];
            foreach ($earningRuleData['excludedSKUs'] as $sku) {
                $skus[] = SKU::deserialize($sku);
            }
            $this->excludedSKUs = $skus;
        }
        if (isset($earningRuleData['excludedLabels'])) {
            $labels = [];
            foreach ($earningRuleData['excludedLabels'] as $label) {
                if ($label == null) {
                    continue;
                }
                $labels[] = Label::deserialize($label);
            }
            $this->excludedLabels = $labels;
        }
        if (isset($earningRuleData['includedLabels'])) {
            $labels = [];
            foreach ($earningRuleData['includedLabels'] as $label) {
                if ($label == null) {
                    continue;
                }
                $labels[] = Label::deserialize($label);
            }
            $this->includedLabels = $labels;
        }
        if (isset($earningRuleData['labelsInclusionType'])) {
            $inclusion = $earningRuleData['labelsInclusionType'];
            $this->labelsInclusionType = $inclusion !== self::LABELS_INCLUSION_TYPE_INCLUDE && $inclusion !== self::LABELS_INCLUSION_TYPE_EXCLUDE ?
                self::LABELS_INCLUSION_TYPE_NONE :
                $inclusion;
        }
        if (isset($earningRuleData['excludeDeliveryCost'])) {
            $this->excludeDeliveryCost = $earningRuleData['excludeDeliveryCost'];
        }
        if (isset($earningRuleData['minOrderValue'])) {
            $this->minOrderValue = $earningRuleData['minOrderValue'];
        }
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
     * @return Label[]
     */
    public function getIncludedLabels(): array
    {
        return $this->includedLabels;
    }

    /**
     * @param Label[] $includedLabels
     */
    public function setIncludedLabels(array $includedLabels)
    {
        $this->includedLabels = $includedLabels;
    }

    /**
     * @return Label[]
     */
    public function getExcludedLabels()
    {
        return $this->excludedLabels;
    }

    /**
     * @param Label[] $excludedLabels
     */
    public function setExcludedLabels($excludedLabels)
    {
        $this->excludedLabels = $excludedLabels;
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
     * @return string
     */
    public function getLabelsInclusionType(): string
    {
        return $this->labelsInclusionType;
    }

    /**
     * @param string $labelsInclusionType
     */
    public function setLabelsInclusionType(string $labelsInclusionType)
    {
        $this->labelsInclusionType = $labelsInclusionType;
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

    public static function validateRequiredData(array $earningRuleData = [])
    {
        parent::validateRequiredData($earningRuleData);
        Assert::keyIsset($earningRuleData, 'pointValue');
        Assert::notBlank($earningRuleData['pointValue']);
    }
}
