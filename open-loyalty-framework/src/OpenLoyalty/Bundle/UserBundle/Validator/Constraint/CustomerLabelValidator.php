<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Validator\Constraint;

use OpenLoyalty\Component\Core\Domain\Model\Label;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class CustomerLabelValidator.
 */
class CustomerLabelValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || !is_array($value)) {
            return;
        }

        foreach ($value as $label) {
            if (!$label instanceof Label) {
                throw new \InvalidArgumentException('Invalid label');
            }

            if (empty($label->getKey())) {
                if ($this->context instanceof ExecutionContextInterface) {
                    $this->context->buildViolation($constraint->message)
                        ->setInvalidValue($value)
                        ->addViolation();
                } else {
                    $this->context->addViolation($constraint->message);
                }

                return;
            }
        }
    }
}
