<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\ReadModel;

use Broadway\ReadModel\InMemory\InMemoryRepository;
use Broadway\ReadModel\Testing\ProjectorScenarioTestCase;
use Broadway\ReadModel\Projector;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\CustomerRepository;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasMovedToLevel;
use OpenLoyalty\Component\Customer\Domain\LevelId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomersBelongingToOneLevel;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomersBelongingToOneLevelProjector;
use OpenLoyalty\Component\Level\Domain\LevelRepository;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CustomersBelongingToOneLevelProjectorTest.
 */
final class CustomersBelongingToOneLevelProjectorTest extends ProjectorScenarioTestCase
{
    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var CustomerId
     */
    protected $customer2Id;

    /**
     * @var LevelId
     */
    protected $levelId;

    /**
     * @var LevelId
     */
    protected $level2Id;

    /**
     * {@inheritdoc}
     */
    protected function createProjector(InMemoryRepository $repository): Projector
    {
        $this->customerId = new CustomerId('00000000-1111-0000-0000-000000000000');
        $this->levelId = new LevelId('00000000-2222-0000-0000-000000000111');

        /** @var Customer|MockObject $customer */
        $customer = $this->getMockBuilder(Customer::class)->getMock();
        $customer->method('getId')->willReturn($this->customerId);
        $customer->method('getLevelId')->willReturn($this->levelId);
        $customer->method('getFirstName')->willReturn('John');
        $customer->method('getLastName')->willReturn('Doe');
        $customer->method('getEmail')->willReturn('john.doe@example.com');

        $this->customer2Id = new CustomerId('00000000-2222-0000-0000-000000000000');
        $this->level2Id = new LevelId('00000000-2222-0000-0000-000000000222');

        /** @var Customer|MockObject $customer2 */
        $customer2 = $this->getMockBuilder(Customer::class)->getMock();
        $customer2->method('getId')->willReturn($this->customerId);
        $customer2->method('getLevelId')->willReturn($this->levelId);
        $customer2->method('getFirstName')->willReturn('John1');
        $customer2->method('getLastName')->willReturn('Doe1');
        $customer2->method('getEmail')->willReturn('john.doe1@example.com');

        /** @var CustomerRepository|MockObject $customerRepository */
        $customerRepository = $this->getMockBuilder(CustomerRepository::class)
            ->disableOriginalConstructor()->getMock();
        $customerRepository->method('load')
            ->with($this->logicalOr(
                $this->equalTo((string) $this->customerId),
                $this->equalTo((string) $this->customer2Id)
            ))
            ->will($this->returnCallback(function ($id) use ($customer, $customer2) {
                if ($id == $customer->getId()) {
                    return $customer;
                }

                return $customer2;
            }));

        /** @var LevelRepository|MockObject $levelRepository */
        $levelRepository = $this->getMockBuilder(LevelRepository::class)->getMock();
        $levelRepository->method('byId')->willReturn(null);

        return new CustomersBelongingToOneLevelProjector($customerRepository, $repository, $levelRepository);
    }

    /**
     * @test
     */
    public function it_add_customer_to_level(): void
    {
        $this->scenario
            ->given([])
            ->when(new CustomerWasMovedToLevel($this->customerId, $this->levelId))
            ->then([
                $this->createBaseReadModel(
                    $this->customerId,
                    $this->levelId,
                    [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'john.doe@example.com',
                    ]
                ),
            ]);
    }

    /**
     * @test
     */
    public function it_changes_customer_level(): void
    {
        $this->scenario
            ->given([
                new CustomerWasMovedToLevel($this->customerId, $this->levelId),
            ])
            ->when(new CustomerWasMovedToLevel($this->customerId, $this->level2Id, $this->levelId))
            ->then([
                $this->createBaseReadModel($this->customerId, $this->levelId, null),
                $this->createBaseReadModel(
                    $this->customerId,
                    $this->level2Id,
                    [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'john.doe@example.com',
                    ]
                ),
            ]);
    }

    /**
     * @test
     */
    public function it_add_multiple_customers_to_one_level(): void
    {
        $this->scenario
            ->given([
                new CustomerWasMovedToLevel($this->customerId, $this->levelId),
            ])
            ->when(new CustomerWasMovedToLevel($this->customer2Id, $this->levelId))
            ->then([
                $this->createBaseReadModelWithMultipleCustomers($this->levelId, [
                    [
                        'id' => $this->customerId,
                        'data' => [
                            'firstName' => 'John',
                            'lastName' => 'Doe',
                            'email' => 'john.doe@example.com',
                        ],
                    ],
                    [
                        'id' => $this->customer2Id,
                        'data' => [
                            'firstName' => 'John1',
                            'lastName' => 'Doe1',
                            'email' => 'john.doe1@example.com',
                        ],
                    ],
                ]),
            ]);
    }

    /**
     * @param CustomerId $customerId
     * @param LevelId    $levelId
     * @param array|null $data
     *
     * @return CustomersBelongingToOneLevel
     */
    private function createBaseReadModel(CustomerId $customerId, LevelId $levelId, array $data = null): CustomersBelongingToOneLevel
    {
        $customersBelongingToOneLevel = new CustomersBelongingToOneLevel($levelId);
        if ($data) {
            $data['id'] = (string) $customerId;
            $customersBelongingToOneLevel->addCustomer(CustomerDetails::deserialize($data));
        }

        return $customersBelongingToOneLevel;
    }

    /**
     * @param LevelId $levelId
     * @param array   $customers
     *
     * @return CustomersBelongingToOneLevel
     */
    private function createBaseReadModelWithMultipleCustomers(LevelId $levelId, array $customers = []): CustomersBelongingToOneLevel
    {
        $customersBelongingToOneLevel = new CustomersBelongingToOneLevel($levelId);
        foreach ($customers as $customer) {
            $data = $customer['data'];
            $data['id'] = (string) $customer['id'];
            $customersBelongingToOneLevel->addCustomer(CustomerDetails::deserialize($data));
        }

        return $customersBelongingToOneLevel;
    }
}
