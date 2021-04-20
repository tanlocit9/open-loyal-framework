<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Method;

use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Service\EmailProvider;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;

/**
 * Class EmailActivationMethod.
 */
class EmailActivationMethod implements ActivationMethod
{
    /**
     * @var EmailProvider
     */
    private $emailProvider;

    /**
     * @var string
     */
    private $frontendCustomerPanelUrl;

    /**
     * @var string
     */
    private $frontendActivationUrl;

    /**
     * EmailActivationMethod constructor.
     *
     * @param EmailProvider $emailProvider
     * @param string        $frontendCustomerPanelUrl
     * @param string        $frontendActivationUrl
     */
    public function __construct(
        EmailProvider $emailProvider,
        string $frontendCustomerPanelUrl,
        string $frontendActivationUrl
    ) {
        $this->emailProvider = $emailProvider;
        $this->frontendCustomerPanelUrl = $frontendCustomerPanelUrl;
        $this->frontendActivationUrl = $frontendActivationUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeUsed()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sendActivationMessage(Customer $user)
    {
        if (!$user->getEmail()) {
            return false;
        }
        $url = $this->frontendCustomerPanelUrl.
            $this->frontendActivationUrl.'/'.$user->getActionToken();

        return $this->emailProvider->registration($user, $url);
    }

    /**
     * {@inheritdoc}
     */
    public function sendTemporaryPassword(CustomerDetails $customer, string $password)
    {
        if (!$customer->getEmail()) {
            return false;
        }

        return $this->emailProvider->registrationWithTemporaryPassword(
            $customer,
            $password
        );
    }

    /**
     * {@inheritdoc}
     */
    public function sendPasswordReset(User $customer, string $token = null)
    {
        if (!$customer->getEmail()) {
            return false;
        }

        $customer->setConfirmationToken($token);
        $customer->setPasswordRequestedAt(new \DateTime());

        return $this->emailProvider->resettingPasswordMessage($customer);
    }
}
