<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Provider;

/**
 * Interface ChoiceProvider.
 */
interface ChoiceProvider
{
    /**
     * @return array
     */
    public function getChoices(): array;

    /**
     * @return string
     */
    public function getType(): string;
}
