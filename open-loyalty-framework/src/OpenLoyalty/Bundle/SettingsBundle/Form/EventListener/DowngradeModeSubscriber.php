<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\SettingsBundle\Form\EventListener;

use OpenLoyalty\Bundle\SettingsBundle\Form\Type\SettingsFormType;
use OpenLoyalty\Bundle\SettingsBundle\Model\Settings;
use OpenLoyalty\Component\Customer\Infrastructure\LevelDowngradeModeProvider;
use OpenLoyalty\Component\Customer\Infrastructure\TierAssignTypeProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class DowngradeModeSubscriber.
 */
class DowngradeModeSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [FormEvents::SUBMIT => '__invoke'];
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
        $levelDowngradeMode = $data->getEntry(SettingsFormType::LEVEL_DOWNGRADE_MODE_SETTINGS_KEY);

        if (!$tierAssignType
            || TierAssignTypeProvider::TYPE_POINTS !== $tierAssignType->getValue()
            || !$levelDowngradeMode
        ) {
            return;
        }

        $levelDowngradeModeValue = $levelDowngradeMode->getValue();
        if (LevelDowngradeModeProvider::MODE_X_DAYS === $levelDowngradeModeValue) {
            $downgradeDays = $data->getEntry(SettingsFormType::LEVEL_DOWNGRADE_DAYS_SETTINGS_KEY);
            if (!$downgradeDays || null === $downgradeDays->getValue()) {
                $event
                    ->getForm()
                    ->get(SettingsFormType::LEVEL_DOWNGRADE_DAYS_SETTINGS_KEY)
                    ->addError(
                        $this->getTranslatedError((new NotBlank())->message)
                    )
                ;
            }

            if ($downgradeDays && is_numeric($downgradeDays->getValue()) && $downgradeDays->getValue() < 1) {
                $minMessage = (new Range(['min' => 1]))->minMessage;
                $event
                    ->getForm()
                    ->get(SettingsFormType::LEVEL_DOWNGRADE_DAYS_SETTINGS_KEY)
                    ->addError(
                        $this->getTranslatedError(
                            $minMessage,
                            [
                                '{{ limit }}' => 1,
                            ]
                        )
                    )
                ;
            }

            $downgradeBase = $data->getEntry(SettingsFormType::LEVEL_DOWNGRADE_BASE_SETTINGS_KEY);
            if (!$downgradeBase || !$downgradeBase->getValue()) {
                $event
                    ->getForm()
                    ->get(SettingsFormType::LEVEL_DOWNGRADE_BASE_SETTINGS_KEY)
                    ->addError(
                        $this->getTranslatedError((new NotBlank())->message)
                    )
                ;
            }
        }
    }

    /**
     * @param string $message
     * @param array  $params
     *
     * @return FormError
     */
    private function getTranslatedError(string $message, array $params = []): FormError
    {
        return new FormError($message, $message, $params);
    }
}
