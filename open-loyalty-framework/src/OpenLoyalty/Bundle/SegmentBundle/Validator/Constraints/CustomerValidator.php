<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Validator\Constraints;

use OpenLoyalty\Bundle\SegmentBundle\Provider\CustomerIdProvider;
use OpenLoyalty\Component\Segment\Domain\Exception\CustomerNotFoundException;
use OpenLoyalty\Component\Segment\Domain\Exception\TooManyCustomersFoundException;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class CustomerValidator.
 */
class CustomerValidator extends ConstraintValidator
{
    /**
     * @var CustomerIdProvider
     */
    private $customerIdProvider;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * CustomerValidator constructor.
     *
     * @param CustomerIdProvider $customerIdProvider
     * @param Translator         $translator
     */
    public function __construct(CustomerIdProvider $customerIdProvider, Translator $translator)
    {
        $this->customerIdProvider = $customerIdProvider;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        try {
            $this->customerIdProvider->getCustomerId($value);
        } catch (TooManyCustomersFoundException | CustomerNotFoundException $e) {
            $this->context->buildViolation($this->translator->trans($e->getMessage(), ['%data%' => $value]))
                ->addViolation();
        }
    }
}
