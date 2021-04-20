<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use Assert\Assertion as Assert;
use Assert\AssertionFailedException;
use OpenLoyalty\Component\Core\Domain\Model\LabelMultiplier;
use OpenLoyalty\Component\EarningRule\Domain\Stoppable\StoppableInterface;

/**
 * Class MultiplyPointsByProductLabelsEarningRule.
 */
class MultiplyPointsByProductLabelsEarningRule extends EarningRule implements StoppableInterface
{
    /**
     * @var LabelMultiplier[]
     */
    protected $labelMultipliers = [];

    /**
     * @param array $earningRuleData
     */
    public function setFromArray(array $earningRuleData = [])
    {
        parent::setFromArray($earningRuleData);

        if (isset($earningRuleData['labelMultipliers'])) {
            $labelMultipliers = [];
            foreach ($earningRuleData['labelMultipliers'] as $labelMultiplier) {
                if ($labelMultiplier == null) {
                    continue;
                }
                $labelMultipliers[] = LabelMultiplier::deserialize($labelMultiplier);
            }
            $this->labelMultipliers = $labelMultipliers;
        }
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
     * @param array $earningRuleData
     *
     * @throws AssertionFailedException
     */
    public static function validateRequiredData(array $earningRuleData = [])
    {
        parent::validateRequiredData($earningRuleData);
        Assert::keyIsset($earningRuleData, 'labelMultipliers');
        Assert::notBlank($earningRuleData['labelMultipliers']);
    }
}
