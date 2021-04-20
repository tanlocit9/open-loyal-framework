<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Webhook\Infrastructure\SystemEvent\Listener;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Component\Webhook\Domain\Command\DispatchWebhook;

/**
 * Class BaseWebhookListener.
 */
abstract class BaseWebhookListener
{
    /**
     * @var array
     */
    private $dispatchedCommandsHash = [];

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @return CommandBus
     */
    public function getCommandBus(): CommandBus
    {
        return $this->commandBus;
    }

    /**
     * @param CommandBus $commandBus
     */
    public function setCommandBus(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param string $type
     * @param array  $data
     */
    public function dispatchCommand(string $type, array $data): void
    {
        $this->cacheDispatchCommand($type, $data);

        $this->commandBus->dispatch(new DispatchWebhook($type, $data));
    }

    /**
     * @param string $type
     * @param array  $data
     */
    public function uniqueDispatchCommand(string $type, array $data): void
    {
        $hash = $this->getCacheHash($type, $data);

        if (!array_key_exists($hash, $this->dispatchedCommandsHash)) {
            $this->dispatchCommand($type, $data);
        }
    }

    /**
     * @param string $type
     * @param array  $data
     *
     * @return string
     */
    protected function getCacheHash(string $type, array $data): string
    {
        return md5(sprintf('%s%s', $type, serialize($data)));
    }

    /**
     * @param string $type
     * @param array  $data
     *
     * @return string
     */
    protected function cacheDispatchCommand(string $type, array $data): string
    {
        $hash = $this->getCacheHash($type, $data);

        $this->dispatchedCommandsHash[$hash] = [
            'type' => $type,
            'data' => $data,
        ];

        return $hash;
    }
}
