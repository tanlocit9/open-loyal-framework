<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Validator\Constraints;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class GeoTypeValidator.
 */
class GeoTypeValidator extends ConstraintValidator
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ImageValidator constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param string     $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $value = str_replace(',', '.', $value);

        $trans = $this->translator->trans('earning_rule.geo_rule.constraints.coordinates_field');
        $valid = true;

        if (is_null($value)) {
            $valid = false;
        }
        if ($value != '0') {
            $value = (float) $value;
            if ($value == 0) {
                $valid = false;
            }
        }
        if (!$valid) {
            $this->context->buildViolation($trans)->addViolation();
        }
    }
}
