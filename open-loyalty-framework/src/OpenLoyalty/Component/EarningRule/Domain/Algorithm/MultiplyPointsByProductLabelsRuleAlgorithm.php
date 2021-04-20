<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Algorithm;

use OpenLoyalty\Component\EarningRule\Domain\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\MultiplyPointsByProductLabelsEarningRule;
use OpenLoyalty\Component\Transaction\Domain\Model\Item;

/**
 * Class MultiplyPointsByProductLabelsRuleAlgorithm.
 */
class MultiplyPointsByProductLabelsRuleAlgorithm extends AbstractRuleAlgorithm
{
    /**
     * MultiplyPointsForProductRuleAlgorithm constructor.
     */
    public function __construct()
    {
        parent::__construct(self::MEDIUM_PRIORITY);
    }

    /**
     * {@inheritdoc}
     *
     * @throws EarningRuleAlgorithmException
     */
    public function evaluate(RuleEvaluationContextInterface $context, EarningRule $rule): bool
    {
        if (!$rule instanceof MultiplyPointsByProductLabelsEarningRule) {
            throw new EarningRuleAlgorithmException(sprintf('"%s" class is not supported', get_class($rule)));
        }

        $arePointsAdded = false;

        foreach ($context->getTransaction()->getItems() as $item) {
            $multiplier = $this->getMultiplier($rule, $item);
            if (null !== $multiplier) {
                $sku = $item->getSku()->getCode();
                $context->setProductPoints($sku, $context->getProductPoints($sku) * $multiplier);
                $arePointsAdded = true;
            }
        }

        if ($arePointsAdded) {
            $context->addEarningRuleName(
                $rule->getEarningRuleId()->__toString(),
                $rule->getName()
            );
        }

        return true;
    }

    /**
     * @param MultiplyPointsByProductLabelsEarningRule $rule
     * @param Item                                     $item
     *
     * @return float|null
     */
    protected function getMultiplier(MultiplyPointsByProductLabelsEarningRule $rule, Item $item): ?float
    {
        $multiplier = null;
        foreach ($rule->getLabelMultipliers() as $labelMultiplier) {
            foreach ($item->getLabels() as $itemLabel) {
                if ($itemLabel->getKey() != $labelMultiplier->getKey()) {
                    continue;
                }

                if ($itemLabel->getValue() != $labelMultiplier->getValue()) {
                    continue;
                }

                if (null === $labelMultiplier->getMultiplier()) {
                    continue;
                }

                $multiplier = (null === $multiplier)
                    ? $labelMultiplier->getMultiplier()
                    : $multiplier * $labelMultiplier->getMultiplier();
            }
        }

        return $multiplier;
    }
}
