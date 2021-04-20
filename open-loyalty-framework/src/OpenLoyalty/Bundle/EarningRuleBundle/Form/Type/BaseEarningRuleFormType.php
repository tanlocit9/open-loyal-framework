<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Form\Type;

use OpenLoyalty\Bundle\EarningRuleBundle\Model\EarningRule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class BaseEarningRuleFormType.
 */
class BaseEarningRuleFormType extends AbstractType
{
    /**
     * @param array                     $value
     * @param ExecutionContextInterface $context
     */
    public function validateTarget($value, ExecutionContextInterface $context)
    {
        if (!$context->getObject()) {
            return;
        }

        /** @var EarningRule $rule */
        $rule = $context->getObject()->getParent()->getNormData();
        if (!$rule) {
            return;
        }

        if ($rule->getType() === EarningRule::TYPE_EVENT && $rule->getEventName() === 'oloy.account.created') {
            if (count($rule->getLevels()) > 0) {
                $context->buildViolation('Level is not empty. Level and segment should be empty.')
                        ->atPath('levels')->addViolation();
            }
            if (count($rule->getSegments()) > 0) {
                $context->buildViolation('Segment is not empty. Segment and level should be empty.')
                        ->atPath('segments')->addViolation();
            }

            return;
        }

        if (count($rule->getLevels()) !== 0 || count($rule->getSegments()) !== 0) {
            return;
        }

        $context->buildViolation('Level is empty. Level or segment should be selected.')
            ->atPath('levels')->addViolation();
        $context->buildViolation('Segment is empty. Segment or level should be selected.')
            ->atPath('segments')->addViolation();
    }
}
