<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Method;

use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;

/**
 * Interface ActivationMethod.
 */
interface ActivationMethod
{
    /**
     * @return bool
     */
    public function canBeUsed();

    /**
     * @param Customer $user
     *
     * @return bool
     */
    public function sendActivationMessage(Customer $user);

    /**
     * @param CustomerDetails $customer
     * @param string          $password
     *
     * @return bool
     */
    public function sendTemporaryPassword(CustomerDetails $customer, string $password);

    /**
     * @param User        $customer
     * @param string|null $token
     *
     * @return bool
     */
    public function sendPasswordReset(User $customer, string $token = null);
}
