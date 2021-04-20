<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Infrastructure\Provider;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Customer\Infrastructure\Provider\CustomerDetailsProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomerDetailsProviderTest.
 */
class CustomerDetailsProviderTest extends TestCase
{
    /**
     * @var CustomerDetailsRepository|MockObject
     */
    private $customerDetailsRepositoryMock;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->customerDetailsRepositoryMock = $this->getMockForAbstractClass(CustomerDetailsRepository::class);
    }

    /**
     * @test
     */
    public function it_provides_customer_details_for_given_customer_id(): void
    {
        /** @var CustomerDetails|MockObject $customerDetailsMock */
        $customerDetailsMock = $this
            ->getMockBuilder(CustomerDetails::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->customerDetailsRepositoryMock->method('find')->willReturn($customerDetailsMock);

        $customerDetailsProvider = new CustomerDetailsProvider($this->customerDetailsRepositoryMock);

        $customerDetails = $customerDetailsProvider->getCustomerDetailsByCustomerId(
            new CustomerId('00000000-0000-0000-0000-000000000000')
        );

        $this->assertSame($customerDetailsMock, $customerDetails);
    }

    /**
     * @test
     */
    public function it_returns_null_when_given_customer_does_not_exist(): void
    {
        $this->customerDetailsRepositoryMock->method('find')->willReturn(null);

        $customerDetailsProvider = new CustomerDetailsProvider($this->customerDetailsRepositoryMock);

        $customer = $customerDetailsProvider->getCustomerDetailsByCustomerId(new CustomerId('00000000-0000-0000-0000-000000000001'));

        $this->assertNull($customer);
    }
}
