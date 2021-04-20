<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Webhook\Infrastructure;

/**
 * Interface WebhookConfigProvider.
 */
interface WebhookConfigProvider
{
    /**
     * @return string
     */
    public function getUri(): string;

    /**
     * @return bool
     */
    public function isEnabled(): bool;
}
