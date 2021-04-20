<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Validator\Constraints;

use OpenLoyalty\Bundle\SettingsBundle\Entity\SettingsEntry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class NotEmptyValueValidator.
 *
 * @Annotation
 */
class NotEmptyValueValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NotEmptyValue) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\NotEmptyValue');
        }

        if (!$value instanceof SettingsEntry) {
            throw new UnexpectedTypeException($value, SettingsEntry::class);
        }

        $realValue = $value->getValue();
        if (false === $realValue || (empty($realValue) && '0' != $realValue)) {
            $this->context->buildViolation($constraint->message)
                          ->setParameter('{{ value }}', $this->formatValue($realValue))
                          ->setCode(NotEmptyValue::IS_BLANK_ERROR)
                          ->addViolation();
        }
    }
}
