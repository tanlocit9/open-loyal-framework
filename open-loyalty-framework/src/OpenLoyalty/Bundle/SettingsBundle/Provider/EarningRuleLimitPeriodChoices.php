<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

use OpenLoyalty\Bundle\EarningRuleBundle\Model\EarningRuleLimit;

/**
 * Class EarningRuleLimitPeriodChoices.
 */
class EarningRuleLimitPeriodChoices implements ChoiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        return ['choices' => [
            '1 day' => EarningRuleLimit::PERIOD_DAY,
            '1 week' => EarningRuleLimit::PERIOD_WEEK,
            '1 month' => EarningRuleLimit::PERIOD_MONTH,
            '3 months' => EarningRuleLimit::PERIOD_3_MONTHS,
            '6 months' => EarningRuleLimit::PERIOD_6_MONTHS,
            '1 year' => EarningRuleLimit::PERIOD_YEAR,
            'forever' => EarningRuleLimit::PERIOD_FOREVER,
        ]];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'earningRuleLimitPeriod';
    }
}
