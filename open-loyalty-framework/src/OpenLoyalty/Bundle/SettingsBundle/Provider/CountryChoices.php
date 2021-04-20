<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

use Symfony\Component\Form\Extension\Core\Type\CountryType;

/**
 * Class CountryChoices.
 */
class CountryChoices implements ChoiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        $type = new CountryType();
        $choiceList = $type->loadChoiceList();
        $choices = $choiceList ? $choiceList->getStructuredValues() : [];

        return ['choices' => $choices];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'country';
    }
}
