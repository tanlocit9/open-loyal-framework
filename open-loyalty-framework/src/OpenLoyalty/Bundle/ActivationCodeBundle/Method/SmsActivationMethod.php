<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Method;

use OpenLoyalty\Bundle\ActivationCodeBundle\Service\ActivationCodeManager;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;

/**
 * Class SmsActivationMethod.
 */
class SmsActivationMethod implements ActivationMethod
{
    /**
     * @var ActivationCodeManager
     */
    private $activationCodeManager;

    /**
     * SmsActivationMethod constructor.
     *
     * @param ActivationCodeManager $activationCodeManager
     */
    public function __construct(ActivationCodeManager $activationCodeManager)
    {
        $this->activationCodeManager = $activationCodeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeUsed()
    {
        return $this->activationCodeManager->hasNeededSettings();
    }

    /**
     * {@inheritdoc}
     */
    public function sendActivationMessage(Customer $user)
    {
        return $this->activationCodeManager->sendCode(
            $this->activationCodeManager->newCode(get_class($user), $user->getId()),
            $user->getPhone()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function sendTemporaryPassword(CustomerDetails $customer, string $password)
    {
        return $this->activationCodeManager->sendTemporaryPassword($password, $customer->getPhone());
    }

    /**
     * {@inheritdoc}
     */
    public function sendPasswordReset(User $customer, string $token = null)
    {
        return $this->activationCodeManager->sendResetCode(
            $this->activationCodeManager->newCode(get_class($customer), $customer->getId()),
            $customer->getPhone()
        );
    }
}
