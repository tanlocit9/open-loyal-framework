<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Provider;

use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Segment\Domain\Model\CustomerDetails;

/**
 * Class SegmentCustomerDetailsProvider.
 */
class CustomerDetailsProvider implements CustomerDetailsProviderInterface
{
    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    /**
     * SegmentCustomerDetailsProvider constructor.
     *
     * @param CustomerDetailsRepository $customerDetailsRepository
     */
    public function __construct(CustomerDetailsRepository $customerDetailsRepository)
    {
        $this->customerDetailsRepository = $customerDetailsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomers(array $customerIds): array
    {
        $customersDetails = $this->customerDetailsRepository->findByIds($customerIds);

        $result = [];

        foreach ($customersDetails as $customerDetails) {
            $result[] = new CustomerDetails(
                $customerDetails->getId(),
                $customerDetails->getEmail(),
                $customerDetails->getPhone()
            );
        }

        return $result;
    }
}
