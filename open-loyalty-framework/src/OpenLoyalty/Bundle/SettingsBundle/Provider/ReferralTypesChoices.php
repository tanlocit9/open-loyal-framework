<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

use OpenLoyalty\Component\EarningRule\Domain\ReferralEarningRule;

/**
 * Class ReferralTypesChoices.
 */
class ReferralTypesChoices implements ChoiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        return [
            'choices' => [
                ReferralEarningRule::TYPE_REFERRED => ReferralEarningRule::TYPE_REFERRED,
                ReferralEarningRule::TYPE_REFERRER => ReferralEarningRule::TYPE_REFERRER,
                ReferralEarningRule::TYPE_BOTH => ReferralEarningRule::TYPE_BOTH,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'referralTypes';
    }
}
