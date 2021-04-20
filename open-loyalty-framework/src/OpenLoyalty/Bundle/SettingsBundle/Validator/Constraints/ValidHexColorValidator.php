<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Validator\Constraints;

use OpenLoyalty\Bundle\SettingsBundle\Entity\SettingsEntry;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidHexColorValidator.
 */
class ValidHexColorValidator extends ConstraintValidator
{
    /**
     * Regex for hex color consist of 3 and 6 characters.
     */
    const HEX_COLOR_REGEX = '/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/';

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        if (!$constraint instanceof ValidHexColor) {
            throw new UnexpectedTypeException($constraint, ValidHexColor::class);
        }

        if (!$value instanceof SettingsEntry) {
            throw new UnexpectedTypeException($value, SettingsEntry::class);
        }

        $realValue = $value->getValue();

        if (!$realValue) {
            return;
        }

        $test = preg_match(self::HEX_COLOR_REGEX, $realValue);
        if (!$test) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
