<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\SystemEvent;

use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class CustomerRegisteredSystemEvent.
 */
class CustomerRegisteredSystemEvent extends CustomerSystemEvent
{
    /**
     * @var array
     */
    private $customerData;

    /**
     * CustomerRegisteredSystemEvent constructor.
     *
     * @param CustomerId $customerId
     * @param array      $customerData
     */
    public function __construct(CustomerId $customerId, array $customerData)
    {
        parent::__construct($customerId);
        $this->customerData = $customerData;
    }

    /**
     * @return array
     */
    public function getCustomerData(): array
    {
        return $this->customerData;
    }
}
