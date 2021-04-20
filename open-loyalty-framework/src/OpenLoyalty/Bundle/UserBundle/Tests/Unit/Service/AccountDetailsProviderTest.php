<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Service;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Bundle\UserBundle\Service\AccountDetailsProvider;
use OpenLoyalty\Bundle\UserBundle\Service\AccountDetailsProviderInterface;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Customer\Domain\CustomerRepository;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use PHPUnit\Framework\TestCase;

/**
 * Class AccountDetailsProviderTest.
 */
class AccountDetailsProviderTest extends TestCase
{
    const CUSTOMER_ID = 'b90ebd9c-8c16-4525-a9de-bea74386dc6d';
    const CUSTOMER2_ID = 'b46ff62f-554b-485e-b92c-84633f2f2b2f';
    const CUSTOMER3_ID = 'b46ff62f-554b-485e-b92c-84633f2f2b20';
    const ACCOUNT_ID = '5dea3a78-87db-42f7-b9f6-a05a20c26e33';
    const AVAILABLE_AMOUNT = 994;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var AccountDetailsProviderInterface
     */
    private $accountDetailsProvider;

    /**
     * @var AccountDetails
     */
    private $accountDetails;

    /**
     * @var AccountDetails
     */
    private $accountDetailsCustomer3;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $customerId = new CustomerId(self::CUSTOMER_ID);
        $this->customer = Customer::registerCustomer($customerId, $this->getCustomerData());
        $customer2 = Customer::registerCustomer(new CustomerId(self::CUSTOMER2_ID), array_merge($this->getCustomerData(), ['id' => self::CUSTOMER2_ID]));
        $customer3 = Customer::registerCustomer(new CustomerId(self::CUSTOMER3_ID), array_merge($this->getCustomerData(), ['id' => self::CUSTOMER3_ID]));

        $customerRepository = $this->getMockBuilder(CustomerRepository::class)->disableOriginalConstructor()->getMock();
        $customerRepository
            ->expects($this->any())
            ->method('load')
            ->will($this->returnValueMap(
                [
                    [self::CUSTOMER_ID, $this->customer],
                    [self::CUSTOMER2_ID, $customer2],
                    [self::CUSTOMER3_ID, $customer3],
                    [self::ACCOUNT_ID, new Customer()],
                ]
            ));
        $this->customerRepository = $customerRepository;

        $accountDetails = $this->getMockBuilder(AccountDetails::class)->disableOriginalConstructor()->getMock();
        $accountDetails
            ->expects($this->any())
            ->method('getAvailableAmount')
            ->willReturn(self::AVAILABLE_AMOUNT);
        $this->accountDetails = $accountDetails;
        $this->accountDetailsCustomer3 = new AccountDetails(new AccountId(self::ACCOUNT_ID), new \OpenLoyalty\Component\Account\Domain\CustomerId(self::CUSTOMER3_ID));

        $accountRepository = $this->getMockBuilder(Repository::class)->disableOriginalConstructor()->getMock();
        $accountRepository
            ->expects($this->any())
            ->method('findBy')
            ->will($this->returnValueMap(
                [
                    [['customerId' => self::CUSTOMER_ID], [$this->accountDetails]],
                    [['customerId' => self::CUSTOMER2_ID], []],
                    [['customerId' => self::CUSTOMER3_ID], [$this->accountDetailsCustomer3]],
                    [['customerId' => self::ACCOUNT_ID], []],
                ]
            ));
        $this->accountRepository = $accountRepository;

        $this->accountDetailsProvider = new AccountDetailsProvider(
            $this->customerRepository,
            $this->accountRepository
        );
    }

    /**
     * @test
     */
    public function it_returns_right_customer_from_repository()
    {
        $customer = $this->accountDetailsProvider->getCustomerById(new CustomerId(self::CUSTOMER_ID));
        $this->assertEquals($customer->getId()->__toString(), self::CUSTOMER_ID);
    }

    /**
     * @test
     */
    public function it_returns_no_customer_from_repository()
    {
        $this->expectException(\Exception::class);
        $this->accountDetailsProvider->getCustomerById(new CustomerId(self::ACCOUNT_ID));
    }

    /**
     * @test
     */
    public function it_returns_right_customer_data()
    {
        $customer = $this->accountDetailsProvider->getCustomerById(new CustomerId(self::CUSTOMER_ID));
        $this->assertEquals($customer->getId()->__toString(), self::CUSTOMER_ID);

        $customerData = $this->getCustomerData();

        $this->assertEquals($customerData['firstName'], $customer->getFirstName());
        $this->assertEquals($customerData['lastName'], $customer->getLastName());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_account_is_not_found()
    {
        $this->expectException(\Exception::class);
        $customer = $this->accountDetailsProvider->getCustomerById(new CustomerId(self::CUSTOMER2_ID));
        $this->accountDetailsProvider->getAccountByCustomer($customer);
    }

    /**
     * @test
     */
    public function it_returns_right_account_related_to_customer()
    {
        $customer = $this->accountDetailsProvider->getCustomerById(new CustomerId(self::CUSTOMER3_ID));
        $account = $this->accountDetailsProvider->getAccountByCustomer($customer);

        $this->assertSame($account, $this->accountDetailsCustomer3);
    }

    /**
     * @test
     */
    public function it_return_correct_active_points_amount()
    {
        $customer = $this->accountDetailsProvider->getCustomerById(new CustomerId(self::CUSTOMER_ID));
        $account = $this->accountDetailsProvider->getAccountByCustomer($customer);
        $this->assertEquals($account->getAvailableAmount(), self::AVAILABLE_AMOUNT);
    }

    /**
     * helper data.
     *
     * @return array
     */
    private function getCustomerData(): array
    {
        return [
            'id' => self::CUSTOMER_ID,
            'firstName' => 'Joe',
            'lastName' => 'Doe',
            'birthDate' => new \DateTime('1999-02-22'),
            'createdAt' => new \DateTime('2018-01-01'),
            'email' => 'user@oloy.com',
            'phone' => '55512344321',
        ];
    }
}
