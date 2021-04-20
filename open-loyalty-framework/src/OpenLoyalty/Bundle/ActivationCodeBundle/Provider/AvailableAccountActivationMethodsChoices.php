<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Provider;

use OpenLoyalty\Bundle\ActivationCodeBundle\Service\SmsSender;
use OpenLoyalty\Bundle\SettingsBundle\Provider\ChoiceProvider;
use OpenLoyalty\Component\Customer\Domain\Model\AccountActivationMethod;

/**
 * Class AvailableAccountActivationMethodsChoices.
 */
class AvailableAccountActivationMethodsChoices implements ChoiceProvider
{
    /**
     * @var AccountActivationMethod
     */
    protected $accountActivationMethod;

    /**
     * @var SmsSender|null
     */
    protected $smsGateway = null;

    /**
     * AvailableAccountActivationMethodsChoices constructor.
     *
     * @param AccountActivationMethod $accountActivationMethod
     * @param SmsSender|null          $smsGateway
     */
    public function __construct(AccountActivationMethod $accountActivationMethod, SmsSender $smsGateway = null)
    {
        $this->accountActivationMethod = $accountActivationMethod;
        $this->smsGateway = $smsGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        $availableAccountActivationMethodsList = $this->accountActivationMethod->getAvailableMethods();
        if (null === $this->smsGateway) {
            if (($key = array_search(AccountActivationMethod::METHOD_SMS, $availableAccountActivationMethodsList)) !== false) {
                unset($availableAccountActivationMethodsList[$key]);
            }
        }

        return ['choices' => $availableAccountActivationMethodsList];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'availableAccountActivationMethods';
    }
}
