<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Form\Transformer;

use OpenLoyalty\Bundle\SegmentBundle\Provider\CustomerIdProvider;
use OpenLoyalty\Component\Segment\Domain\Exception\CustomerNotFoundException;
use OpenLoyalty\Component\Segment\Domain\Exception\TooManyCustomersFoundException;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class CustomerTransformer.
 */
class CustomerTransformer implements DataTransformerInterface
{
    /**
     * @var CustomerIdProvider
     */
    private $customerIdProvider;

    /**
     * CustomerTransformer constructor.
     *
     * @param CustomerIdProvider $customerIdProvider
     */
    public function __construct(CustomerIdProvider $customerIdProvider)
    {
        $this->customerIdProvider = $customerIdProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value === null) {
            return $value;
        }

        try {
            return $this->customerIdProvider->getCustomerId($value);
        } catch (TooManyCustomersFoundException | CustomerNotFoundException $e) {
            return $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ($value === null) {
            return $value;
        }

        try {
            return $this->customerIdProvider->getCustomerId($value);
        } catch (TooManyCustomersFoundException | CustomerNotFoundException $e) {
            return $value;
        }
    }
}
