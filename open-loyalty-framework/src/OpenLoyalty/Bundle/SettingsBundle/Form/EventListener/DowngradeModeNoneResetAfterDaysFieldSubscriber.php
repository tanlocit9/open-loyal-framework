<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\SettingsBundle\Form\EventListener;

use OpenLoyalty\Bundle\SettingsBundle\Entity\BooleanSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Entity\IntegerSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Entity\StringSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Form\Type\SettingsFormType;
use OpenLoyalty\Bundle\SettingsBundle\Model\Settings;
use OpenLoyalty\Component\Customer\Infrastructure\LevelDowngradeModeProvider;
use OpenLoyalty\Component\Customer\Infrastructure\TierAssignTypeProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class DowngradeModeNoneResetAfterDaysFieldSubscriber.
 */
class DowngradeModeNoneResetAfterDaysFieldSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::SUBMIT => '__invoke',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function __invoke(FormEvent $event): void
    {
        $data = $event->getData();
        if (!$data instanceof Settings) {
            return;
        }

        $tierAssignType = $data->getEntry(SettingsFormType::TIER_ASSIGN_TYPE_SETTINGS_KEY);

        if (TierAssignTypeProvider::TYPE_TRANSACTIONS === $tierAssignType->getValue()) {
            $data->addEntry(
                new StringSettingEntry(
                    SettingsFormType::LEVEL_DOWNGRADE_MODE_SETTINGS_KEY,
                    LevelDowngradeModeProvider::MODE_NONE
                )
            );
            $this->resetSettings($data);
        }

        $levelDowngradeMode = $data->getEntry(SettingsFormType::LEVEL_DOWNGRADE_MODE_SETTINGS_KEY);
        $value = $levelDowngradeMode->getValue();

        if (LevelDowngradeModeProvider::MODE_X_DAYS !== $value) {
            $this->resetSettings($data);
        }

        $event->setData($data);
    }

    /**
     * @param Settings $data
     */
    private function resetSettings(Settings $data): void
    {
        $data->addEntry(
            new IntegerSettingEntry(
                SettingsFormType::LEVEL_DOWNGRADE_DAYS_SETTINGS_KEY,
                ''
            )
        );
        $data->addEntry(
            new StringSettingEntry(
                SettingsFormType::LEVEL_DOWNGRADE_BASE_SETTINGS_KEY,
                LevelDowngradeModeProvider::BASE_NONE
            )
        );
        $data->addEntry(
            new BooleanSettingEntry(
                SettingsFormType::LEVEL_RESET_POINTS_ON_DOWNGRADE_SETTINGS_KEY,
                false
            )
        );
    }
}
