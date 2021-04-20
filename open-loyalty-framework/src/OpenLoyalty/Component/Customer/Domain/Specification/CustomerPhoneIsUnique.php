<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Domain\Specification;

use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;

/**
 * Class CustomerPhoneIsUnique.
 */
class CustomerPhoneIsUnique implements CustomerPhoneSpecificationInterface
{
    /**
     * @var CustomerDetailsRepository
     */
    private $customerRepository;

    /**
     * CustomerPhoneIsUnique constructor.
     *
     * @param CustomerDetailsRepository $customerRepository
     */
    public function __construct(CustomerDetailsRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy(string $phoneNumber, ?string $customerId = null): bool
    {
        $phoneNumber = str_replace('+', '', $phoneNumber);

        $results = $this->customerRepository->findOneByPhone($phoneNumber, $customerId);

        return count($results) === 0;
    }
}
