<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

use Symfony\Component\Form\Extension\Core\Type\TimezoneType;

/**
 * Class TimezoneChoices.
 */
class TimezoneChoices implements ChoiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        $type = new TimezoneType();
        $choiceList = $type->loadChoiceList();
        $choices = $choiceList ? $choiceList->getStructuredValues() : [];

        return ['choices' => $choices];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'timezone';
    }
}
