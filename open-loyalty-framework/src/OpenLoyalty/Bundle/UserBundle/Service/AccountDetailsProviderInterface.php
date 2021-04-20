<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Service;

use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Interface AccountDetailsProviderInterface.
 */
interface AccountDetailsProviderInterface
{
    /**
     * @param CustomerId $customerId
     *
     * @return null|Customer
     */
    public function getCustomerById(CustomerId $customerId): ?Customer;

    /**
     * @param Customer $customer
     *
     * @return AccountDetails
     */
    public function getAccountByCustomer(Customer $customer): AccountDetails;

    /**
     * @param CustomerId $customerId
     *
     * @return AccountDetails
     */
    public function getAccountByCustomerId(CustomerId $customerId): AccountDetails;
}
