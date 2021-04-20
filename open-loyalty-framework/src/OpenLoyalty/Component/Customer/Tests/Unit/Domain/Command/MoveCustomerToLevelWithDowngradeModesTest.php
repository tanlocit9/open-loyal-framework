<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\Command;

use Broadway\CommandHandling\CommandHandler;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\EventHandling\EventBus;
use Broadway\EventStore\EventStore;
use OpenLoyalty\Bundle\AuditBundle\Service\AuditManagerInterface;
use OpenLoyalty\Component\Customer\Infrastructure\LevelDowngradeModeProvider;
use OpenLoyalty\Component\Customer\Domain\Command\MoveCustomerToLevel;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerLevelWasRecalculated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasMovedToLevel;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasRegistered;
use OpenLoyalty\Component\Customer\Domain\LevelId;

/**
 * Class MoveCustomerToLevelWithDowngradeModesTest.
 */
final class MoveCustomerToLevelWithDowngradeModesTest extends CustomerCommandHandlerTest
{
    /**
     * {@inheritdoc}
     */
    protected function createCommandHandler(EventStore $eventStore, EventBus $eventBus, AuditManagerInterface $auditManager = null): CommandHandler
    {
        $eventDispatcher = $this->getMockBuilder(EventDispatcher::class)->getMock();
        $eventDispatcher->method('dispatch')->with($this->isType('string'))->willReturn(true);
        $levelDowngradeModeProvider = $this->getMockBuilder(LevelDowngradeModeProvider::class)->getMock();
        $levelDowngradeModeProvider->method('getBase')->willReturn(LevelDowngradeModeProvider::BASE_EARNED_POINTS_SINCE_LAST_LEVEL_CHANGE);
        $levelDowngradeModeProvider->method('getMode')->willReturn(LevelDowngradeModeProvider::MODE_X_DAYS);

        if (null === $auditManager) {
            $auditManager = $this->getMockBuilder(AuditManagerInterface::class)->getMock();
        }

        return $this->getCustomerCommandHandler($eventStore, $eventBus, $eventDispatcher, $auditManager, $levelDowngradeModeProvider);
    }

    /**
     * @test
     */
    public function it_moves_customer_to_level_automatically_and_save_date(): void
    {
        $levelId = new LevelId('00000000-2222-0000-0000-000000000000');
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');
        $date = new \DateTime();
        $this->scenario
            ->withAggregateId((string) $customerId)
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
            ])
            ->when(new MoveCustomerToLevel($customerId, $levelId, null, false, false, $date))
            ->then([
                new CustomerWasMovedToLevel($customerId, $levelId),
                new CustomerLevelWasRecalculated($customerId, $date),
            ]);
    }
}
