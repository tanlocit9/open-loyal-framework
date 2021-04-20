<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Algorithm;

use OpenLoyalty\Component\EarningRule\Domain\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\MultiplyPointsForProductEarningRule;
use OpenLoyalty\Component\Transaction\Domain\Model\Item;

/**
 * Class MultiplyPointsForProductRuleAlgorithm.
 */
class MultiplyPointsForProductRuleAlgorithm extends AbstractRuleAlgorithm
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
     */
    public function evaluate(RuleEvaluationContextInterface $context, EarningRule $rule): bool
    {
        if (!$rule instanceof MultiplyPointsForProductEarningRule) {
            throw new \InvalidArgumentException(get_class($rule));
        }

        $arePointsAdded = false;

        foreach ($context->getTransaction()->getItems() as $item) {
            $sku = $item->getSku()->getCode();

            if (in_array($sku, $rule->getSkuIds()) || $this->getItemHasLabel($rule, $item)) {
                $context->setProductPoints($sku, $context->getProductPoints($sku) * $rule->getMultiplier());
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
     * @param MultiplyPointsForProductEarningRule $rule
     * @param Item                                $item
     *
     * @return bool
     */
    protected function getItemHasLabel(MultiplyPointsForProductEarningRule $rule, Item $item)
    {
        foreach ($rule->getLabels() as $label) {
            foreach ($item->getLabels() as $itemLabel) {
                if ($itemLabel->getKey() == $label->getKey() && $itemLabel->getValue() == $label->getValue()) {
                    return true;
                }
            }
        }

        return false;
    }
}
