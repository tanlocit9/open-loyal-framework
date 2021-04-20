<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AvailablePointExpireAfterSettingChoices.
 */
class AvailablePointExpireAfterChoices implements ChoiceProvider
{
    private const PROVIDER_TYPE = 'availablePointExpireAfter';

    /**
     * @var TranslatorInterface
     */
    private $translation;

    /**
     * AvailablePointExpireAfterSettingChoices constructor.
     *
     * @param TranslatorInterface $translation
     */
    public function __construct(TranslatorInterface $translation)
    {
        $this->translation = $translation;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        return [
            'choices' => [
                AddPointsTransfer::TYPE_ALL_TIME_ACTIVE => $this->translation->trans('settings.all_time_active'),
                AddPointsTransfer::TYPE_AFTER_X_DAYS => $this->translation->trans('settings.after_x_days'),
                AddPointsTransfer::TYPE_AT_MONTH_END => $this->translation->trans('settings.at_the_end_of_the_month'),
                AddPointsTransfer::TYPE_AT_YEAR_END => $this->translation->trans('settings.at_the_end_of_the_year'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return self::PROVIDER_TYPE;
    }
}
