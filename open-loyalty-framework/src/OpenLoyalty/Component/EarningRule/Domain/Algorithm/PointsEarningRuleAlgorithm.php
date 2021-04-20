<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Algorithm;

use OpenLoyalty\Component\EarningRule\Domain\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\PointsEarningRule;

/**
 * Class PointsEarningRuleAlgorithm.
 */
class PointsEarningRuleAlgorithm extends AbstractRuleAlgorithm
{
    /**
     * PointsEarningRuleAlgorithm constructor.
     */
    public function __construct()
    {
        parent::__construct(self::HIGH_PRIORITY);
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate(RuleEvaluationContextInterface $context, EarningRule $rule): bool
    {
        if (!$rule instanceof PointsEarningRule) {
            throw new \InvalidArgumentException(get_class($rule));
        }

        $totalValue = $rule->isExcludeDeliveryCost()
            ? $context->getTransaction()->getGrossValueWithoutDeliveryCosts()
            : $context->getTransaction()->getGrossValue();

        // skip transaction bellow min order value
        if (!empty($rule->getMinOrderValue()) && $totalValue < $rule->getMinOrderValue()) {
            return false;
        }

        $inclusionType = $rule->getLabelsInclusionType();
        $excludeLabels = PointsEarningRule::LABELS_INCLUSION_TYPE_EXCLUDE === $inclusionType ? $rule->getExcludedLabels() : [];
        $includeLabels = PointsEarningRule::LABELS_INCLUSION_TYPE_INCLUDE === $inclusionType ? $rule->getIncludedLabels() : [];

        $filteredItems = $context->getTransaction()->getFilteredItems(
            $rule->getExcludedSKUs(),
            $excludeLabels,
            $includeLabels,
            $rule->isExcludeDeliveryCost()
        );

        foreach ($filteredItems as $item) {
            $context->addProductPoints(
                $item->getSku()->getCode(),
                $item->getGrossValue() * $rule->getPointValue()
            );
        }

        if (count($filteredItems) > 0) {
            $context->addEarningRuleName(
                $rule->getEarningRuleId()->__toString(),
                $rule->getName()
            );
        }

        return true;
    }
}
