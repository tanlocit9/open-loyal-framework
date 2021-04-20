<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Webhook\Infrastructure\Client\Request\Header;

/**
 * Interface RequestHeaderInterface.
 */
interface RequestHeaderInterface
{
    /**
     * @return array
     */
    public function headers(): array;
}
