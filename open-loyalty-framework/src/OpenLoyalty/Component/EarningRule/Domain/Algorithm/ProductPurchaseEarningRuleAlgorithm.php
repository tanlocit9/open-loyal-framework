<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Algorithm;

use OpenLoyalty\Component\EarningRule\Domain\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\ProductPurchaseEarningRule;

/**
 * Class ProductPurchaseEarningRuleAlgorithm.
 */
class ProductPurchaseEarningRuleAlgorithm extends AbstractRuleAlgorithm
{
    /**
     * ProductPurchaseEarningRuleAlgorithm constructor.
     */
    public function __construct()
    {
        parent::__construct(self::LOW_PRIORITY);
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate(RuleEvaluationContextInterface $context, EarningRule $rule): bool
    {
        if (!$rule instanceof ProductPurchaseEarningRule) {
            throw new \InvalidArgumentException(get_class($rule));
        }

        $arePointsAdded = false;

        foreach ($context->getTransaction()->getItems() as $item) {
            $skuCode = $item->getSku()->getCode();
            if (in_array($skuCode, $rule->getSkuIds())) {
                $context->addProductPoints($skuCode, $rule->getPointsAmount());
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
}
