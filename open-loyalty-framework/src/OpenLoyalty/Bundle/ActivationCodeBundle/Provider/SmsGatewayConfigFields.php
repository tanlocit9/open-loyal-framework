<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Provider;

use OpenLoyalty\Bundle\ActivationCodeBundle\Service\SmsSender;
use OpenLoyalty\Bundle\SettingsBundle\Provider\ChoiceProvider;

/**
 * Class SmsGatewayConfigFields.
 */
class SmsGatewayConfigFields implements ChoiceProvider
{
    /**
     * @var SmsSender|null
     */
    protected $smsGateway = null;

    /**
     * SmsGatewayConfigFields constructor.
     *
     * @param SmsSender|null $smsGateway
     */
    public function __construct(SmsSender $smsGateway = null)
    {
        $this->smsGateway = $smsGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        $fields = [];

        if (null !== $this->smsGateway) {
            $fields = $this->smsGateway->getNeededSettings();
        }

        return ['fields' => $fields];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'smsGatewayConfig';
    }
}
