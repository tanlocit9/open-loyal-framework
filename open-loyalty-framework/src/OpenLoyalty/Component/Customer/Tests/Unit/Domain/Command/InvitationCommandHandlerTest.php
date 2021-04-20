<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\Command;

use Broadway\CommandHandling\CommandHandler;
use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\EventHandling\EventBus;
use Broadway\EventStore\EventStore;
use OpenLoyalty\Component\Customer\Domain\Command\InvitationCommandHandler;
use OpenLoyalty\Component\Customer\Domain\InvitationRepository;
use OpenLoyalty\Component\Customer\Domain\Service\InvitationTokenGenerator;

/**
 * Class InvitationCommandHandlerTest.
 */
abstract class InvitationCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createCommandHandler(EventStore $eventStore, EventBus $eventBus): CommandHandler
    {
        $tokenGenerator = $this->getMockBuilder(InvitationTokenGenerator::class)->disableOriginalConstructor()
            ->getMock();
        $tokenGenerator->method('generate')->willReturn('123');
        $dispatcher = $this->getMockBuilder(EventDispatcher::class)->disableOriginalConstructor()
            ->getMock();

        return new InvitationCommandHandler(
            new InvitationRepository($eventStore, $eventBus),
            $tokenGenerator,
            $dispatcher
        );
    }
}
