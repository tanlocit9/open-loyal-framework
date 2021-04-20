<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Validator\Constraints;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ImageValidator as BaseImageValidator;

/**
 * Class ImageValidator.
 */
class ImageValidator extends BaseImageValidator
{
    /**
     * Minimum acceptable height.
     *
     * @var int
     */
    protected $minWidth;

    /**
     * Maximum acceptable height.
     *
     * @var int
     */
    protected $minHeight;

    /**
     * ImageValidator constructor.
     *
     * @param int $minWidth
     * @param int $minHeight
     */
    public function __construct(int $minWidth, int $minHeight)
    {
        $this->minWidth = $minWidth;
        $this->minHeight = $minHeight;
    }

    /**
     * @param int        $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Image) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Image');
        }

        if (null === $constraint->minHeight) {
            $constraint->minHeight = $this->minHeight;
        }

        if (null === $constraint->minWidth) {
            $constraint->minWidth = $this->minWidth;
        }

        parent::validate($value, $constraint);
    }
}
