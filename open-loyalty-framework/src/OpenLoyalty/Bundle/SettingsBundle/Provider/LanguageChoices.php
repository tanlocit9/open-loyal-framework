<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

use Symfony\Component\Form\Extension\Core\Type\LanguageType;

/**
 * Class LanguageChoices.
 */
class LanguageChoices implements ChoiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getChoices(): array
    {
        $type = new LanguageType();
        $choiceList = $type->loadChoiceList();
        $choices = $choiceList ? $choiceList->getStructuredValues() : [];

        return ['choices' => $choices];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'language';
    }
}
