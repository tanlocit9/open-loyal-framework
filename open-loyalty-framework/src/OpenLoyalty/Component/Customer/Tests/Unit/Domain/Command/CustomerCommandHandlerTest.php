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
use Broadway\ReadModel\Repository;
use Broadway\Snapshotting\Snapshot\SnapshotRepository;
use Broadway\Snapshotting\Snapshot\Trigger;
use OpenLoyalty\Bundle\AuditBundle\Service\AuditManagerInterface;
use OpenLoyalty\Component\Customer\Domain\Command\CustomerCommandHandler;
use OpenLoyalty\Component\Customer\Domain\CustomerRepository;
use OpenLoyalty\Component\Customer\Domain\EventSourcedCustomerRepository;
use OpenLoyalty\Component\Customer\Domain\Specification\CustomerPhoneSpecificationInterface;
use OpenLoyalty\Component\Customer\Domain\Validator\CustomerUniqueValidator;
use OpenLoyalty\Component\Customer\Infrastructure\LevelDowngradeModeProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CustomerCommandHandlerTest.
 */
abstract class CustomerCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createCommandHandler(EventStore $eventStore, EventBus $eventBus, AuditManagerInterface $auditManager = null): CommandHandler
    {
        $eventDispatcher = $this->getMockBuilder(EventDispatcher::class)->getMock();
        $eventDispatcher->method('dispatch')->with($this->isType('string'))->willReturn(true);

        if (null === $auditManager) {
            $auditManager = $this->getMockBuilder(AuditManagerInterface::class)->getMock();
        }

        return $this->getCustomerCommandHandler($eventStore, $eventBus, $eventDispatcher, $auditManager, null);
    }

    public static function getCustomerData()
    {
        return [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'gender' => 'male',
            'email' => 'customer@open.com',
            'birthDate' => 653011200,
            'phone' => '+48123123123',
            'createdAt' => 1470646394,
            'loyaltyCardNumber' => '000000',
            'updatedAt' => 1470646394,
            'agreement1' => true,
            'company' => [
                'name' => 'test',
                'nip' => 'nip',
            ],
            'address' => [
                'street' => 'Dmowskiego',
                'address1' => '21',
                'city' => 'Wrocław',
                'country' => 'PL',
                'postal' => '50-300',
                'province' => 'Dolnośląskie',
            ],
            'status' => [
                'type' => 'new',
            ],
        ];
    }

    /**
     * @param EventStore                 $eventStore
     * @param EventBus                   $eventBus
     * @param EventDispatcher            $eventDispatcher
     * @param AuditManagerInterface      $auditManager
     * @param LevelDowngradeModeProvider $levelDowngradeModeProvider
     *
     * @return \OpenLoyalty\Component\Customer\Domain\Command\CustomerCommandHandler
     */
    protected function getCustomerCommandHandler(
        EventStore $eventStore,
        EventBus $eventBus,
        EventDispatcher $eventDispatcher,
        AuditManagerInterface $auditManager = null,
        LevelDowngradeModeProvider $levelDowngradeModeProvider = null)
    {
        /** @var Repository|MockObject $customerDetailsRepository */
        $eventSourcedRepository = new EventSourcedCustomerRepository($eventStore, $eventBus);

        /** @var Trigger|\PHPUnit_Framework_MockObject_MockObject $trigger */
        $trigger = $this->getMockBuilder(Trigger::class)->getMock();

        /** @var SnapshotRepository|\PHPUnit_Framework_MockObject_MockObject $snapshotRepository */
        $snapshotRepository = $this->getMockBuilder(SnapshotRepository::class)->getMock();

        /** @var Repository|\PHPUnit_Framework_MockObject_MockObject $customerDetailsRepository */
        $customerDetailsRepository = $this->getMockBuilder(Repository::class)->getMock();
        $customerDetailsRepository->method('findBy')->willReturn([]);

        /** @var CustomerPhoneSpecificationInterface|MockObject $customerSpecification */
        $customerSpecification = $this->createMock(CustomerPhoneSpecificationInterface::class);
        $customerSpecification->method('isSatisfiedBy')->willReturn(true);

        $validator = new CustomerUniqueValidator($customerDetailsRepository, $customerSpecification);

        if (null === $auditManager) {
            $auditManager = $this->getMockBuilder(AuditManagerInterface::class)->getMock();
        }

        if (null === $levelDowngradeModeProvider) {
            $levelDowngradeModeProvider = $this->getMockBuilder(LevelDowngradeModeProvider::class)->getMock();
            $levelDowngradeModeProvider->method('getBase')->willReturn(LevelDowngradeModeProvider::BASE_ACTIVE_POINTS);
            $levelDowngradeModeProvider->method('getMode')->willReturn(LevelDowngradeModeProvider::MODE_NONE);
        }

        return new CustomerCommandHandler(
            new CustomerRepository($eventSourcedRepository, $eventStore, $snapshotRepository, $trigger),
            $validator,
            $eventDispatcher,
            $auditManager,
            $levelDowngradeModeProvider
        );
    }
}
