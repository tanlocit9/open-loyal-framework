<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

use OpenLoyalty\Component\EarningRule\Domain\ReferralEarningRule;

/**
 * Class ReferralEventsChoices.
 */
class ReferralEventsChoices implements ChoiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        return ['choices' => [
            ReferralEarningRule::EVENT_REGISTER => ReferralEarningRule::EVENT_REGISTER,
            ReferralEarningRule::EVENT_FIRST_PURCHASE => ReferralEarningRule::EVENT_FIRST_PURCHASE,
            ReferralEarningRule::EVENT_EVERY_PURCHASE => ReferralEarningRule::EVENT_EVERY_PURCHASE,
        ]];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'referralEvents';
    }
}
