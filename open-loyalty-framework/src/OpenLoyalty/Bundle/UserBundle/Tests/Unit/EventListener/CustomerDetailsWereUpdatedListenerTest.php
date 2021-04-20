<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\EventListener;

use Broadway\Repository\Repository;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer as CustomerEntity;
use OpenLoyalty\Bundle\UserBundle\EventListener\CustomerDetailsWereUpdatedListener;
use OpenLoyalty\Bundle\UserBundle\Service\UserManager;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerUpdatedSystemEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomerDetailsWereUpdatedListenerTest.
 */
class CustomerDetailsWereUpdatedListenerTest extends TestCase
{
    /**
     * @test
     */
    public function it_updates_entity_email_and_phone_when_domain_is_updated(): void
    {
        /** @var CustomerEntity|MockObject $customerEntityMock */
        $customerEntityMock = $this->getMockBuilder(CustomerEntity::class)->disableOriginalConstructor()->getMock();
        $customerEntityMock->expects($this->once())->method('setPhone');
        $customerEntityMock->expects($this->once())->method('setEmail');

        /** @var ObjectRepository|MockObject $objectRepositoryMock */
        $objectRepositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $objectRepositoryMock->expects($this->once())->method('find')->willReturn($customerEntityMock);

        /** @var EntityManagerInterface|MockObject $entityManagerMock */
        $entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $entityManagerMock->expects($this->once())->method('getRepository')->willReturn($objectRepositoryMock);

        /** @var Customer|MockObject $customer */
        $customer = $this->getMockBuilder(Customer::class)->getMock();
        $customer->expects($this->once())->method('getPhone');
        $customer->expects($this->once())->method('getEmail');

        /** @var Repository|MockObject $customerRepositoryMock */
        $customerRepositoryMock = $this->getMockBuilder(Repository::class)->getMock();
        $customerRepositoryMock->expects($this->once())->method('load')->willReturn($customer);

        /** @var UserManager|MockObject $userManagerMock */
        $userManagerMock = $this->getMockBuilder(UserManager::class)->disableOriginalConstructor()->getMock();
        $userManagerMock->expects($this->once())->method('updateUser');

        $customerDetailsWereUpdatedListener = new CustomerDetailsWereUpdatedListener(
            $entityManagerMock,
            $customerRepositoryMock,
            $userManagerMock
        );

        $customerDetailsWereUpdatedListener->handle(new CustomerUpdatedSystemEvent(new CustomerId(
            '00000000-0000-0000-0000-000000000000'
        )));
    }
}
