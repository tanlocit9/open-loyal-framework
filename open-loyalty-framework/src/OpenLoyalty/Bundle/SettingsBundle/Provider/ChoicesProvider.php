<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

/**
 * Class ChoicesProvider.
 */
class ChoicesProvider
{
    /**
     * @var ChoiceProvider[]
     */
    protected $providers = [];

    /**
     * @param ChoiceProvider $choiceProvider
     */
    public function addChoiceProvider(ChoiceProvider $choiceProvider)
    {
        $this->providers[] = $choiceProvider;
    }

    public function getChoices(string $type): array
    {
        foreach ($this->providers as $provider) {
            if ($provider->getType() !== $type) {
                continue;
            }

            return $provider->getChoices();
        }

        return [];
    }
}
