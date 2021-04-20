<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Provider;

use OpenLoyalty\Component\Customer\Domain\Exception\TooManyResultsException;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Segment\Domain\Exception\CustomerNotFoundException;
use OpenLoyalty\Component\Segment\Domain\Exception\TooManyCustomersFoundException;

/**
 * Class CustomerIdProvider.
 */
class CustomerIdProvider
{
    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    /**
     * CustomerIdProvider constructor.
     *
     * @param CustomerDetailsRepository $customerDetailsRepository
     */
    public function __construct(CustomerDetailsRepository $customerDetailsRepository)
    {
        $this->customerDetailsRepository = $customerDetailsRepository;
    }

    /**
     * @param string $value
     *
     * @return string
     *
     * @throws TooManyCustomersFoundException
     * @throws CustomerNotFoundException
     */
    public function getCustomerId(string $value): string
    {
        try {
            /** @var CustomerDetails $customerDetails */
            $customerDetails = $this->customerDetailsRepository->find($value);
            if ($customerDetails) {
                return $customerDetails->getId();
            }

            $customerDetails = $this->customerDetailsRepository->findOneByCriteria(['email' => strtolower($value)], 1);
            if (!empty($customerDetails)) {
                return $customerDetails[0]->getId();
            }

            $customerDetails = $this->customerDetailsRepository->findOneByCriteria(['phone' => strtolower($value)], 1);
            if (!empty($customerDetails)) {
                return $customerDetails[0]->getId();
            }

            $customerDetails = $this->customerDetailsRepository->findOneByCriteria(['loyaltyCardNumber' => strtolower($value)], 1);
            if (!empty($customerDetails)) {
                return $customerDetails[0]->getId();
            }
        } catch (TooManyResultsException $e) {
            throw new TooManyCustomersFoundException();
        }
        throw new CustomerNotFoundException();
    }
}
