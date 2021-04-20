<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Webhook\Infrastructure\Client;

/**
 * Interface WebhookClientInterface.
 */
interface WebhookClientInterface
{
    /**
     * @param string $uri
     * @param array  $data
     * @param array  $config
     */
    public function postAction(string $uri, array $data, array $config = []): void;
}
