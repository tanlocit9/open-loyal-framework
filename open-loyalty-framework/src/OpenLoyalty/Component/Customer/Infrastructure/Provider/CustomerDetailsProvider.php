<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Infrastructure\Provider;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;

/**
 * Class CustomerDetailsProvider.
 */
class CustomerDetailsProvider implements CustomerDetailsProviderInterface
{
    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    /**
     * CustomerDetailsProvider constructor.
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
    public function getCustomerDetailsByCustomerId(CustomerId $customerId): ?CustomerDetails
    {
        /** @var CustomerDetails $customerDetails */
        $customerDetails = $this->customerDetailsRepository->find((string) $customerId);

        return $customerDetails;
    }
}
