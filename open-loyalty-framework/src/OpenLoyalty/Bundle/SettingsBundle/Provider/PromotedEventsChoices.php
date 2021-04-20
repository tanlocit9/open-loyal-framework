<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

use OpenLoyalty\Component\Account\Domain\SystemEvent\AccountSystemEvents;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerSystemEvents;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\TransactionSystemEvents;

/**
 * Class PromotedEventsChoices.
 */
class PromotedEventsChoices implements ChoiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        return ['choices' => [
            'Customer logged in' => CustomerSystemEvents::CUSTOMER_LOGGED_IN,
            'First purchase' => TransactionSystemEvents::CUSTOMER_FIRST_TRANSACTION,
            'Account created' => AccountSystemEvents::ACCOUNT_CREATED,
            'Newsletter subscription' => CustomerSystemEvents::NEWSLETTER_SUBSCRIPTION,
        ]];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'promotedEvents';
    }
}
