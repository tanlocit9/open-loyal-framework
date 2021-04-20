<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Webhook\Tests\Unit\Infrastructure\SystemEvent\Listener;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Component\Webhook\Infrastructure\SystemEvent\Listener\BaseWebhookListener;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseWebhookListenerTest.
 */
class BaseWebhookListenerTest extends TestCase
{
    /**
     * @test
     */
    public function it_dispatch_only_one_command()
    {
        $commandBusMock = $this->getMockBuilder(CommandBus::class)->getMock();
        $commandBusMock->expects($this->once())->method('dispatch');

        /** @var BaseWebhookListener $mock */
        $mock = $this->getMockBuilder(BaseWebhookListener::class)->getMockForAbstractClass();
        $mock->setCommandBus($commandBusMock);

        $mock->uniqueDispatchCommand('test', ['key' => 'value']);
        $mock->uniqueDispatchCommand('test', ['key' => 'value']);
    }

    /**
     * @test
     */
    public function it_dispatch_command_always()
    {
        $commandBusMock = $this->getMockBuilder(CommandBus::class)->getMock();
        $commandBusMock->expects($this->exactly(2))->method('dispatch');

        /** @var BaseWebhookListener $mock */
        $mock = $this->getMockBuilder(BaseWebhookListener::class)->getMockForAbstractClass();
        $mock->setCommandBus($commandBusMock);

        $mock->dispatchCommand('test', ['key' => 'value']);
        $mock->dispatchCommand('test', ['key' => 'value']);
    }

    /**
     * @test
     */
    public function it_dispatch_one_command_for_key()
    {
        $commandBusMock = $this->getMockBuilder(CommandBus::class)->getMock();
        $commandBusMock->expects($this->exactly(4))->method('dispatch');

        /** @var BaseWebhookListener $mock */
        $mock = $this->getMockBuilder(BaseWebhookListener::class)->getMockForAbstractClass();
        $mock->setCommandBus($commandBusMock);

        $mock->uniqueDispatchCommand('test', ['key' => 'value']);
        $mock->uniqueDispatchCommand('test', ['key' => 'value2']);
        $mock->uniqueDispatchCommand('test2', ['key' => 'value']);
        $mock->uniqueDispatchCommand('test', ['key2' => 'value']);
    }
}
