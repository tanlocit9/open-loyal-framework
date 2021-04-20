<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UtilityBundle\Tests\Integration\Service;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Bundle\UserBundle\Service\AccountDetailsProviderInterface;
use OpenLoyalty\Bundle\UtilityBundle\Service\CustomerDetailsCsvFormatter;
use OpenLoyalty\Component\Account\Domain\Account;
use OpenLoyalty\Component\Account\Domain\CustomerId as AccountCustomerId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Campaign\Domain\SegmentId;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomersBelongingToOneLevel;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Segment\Domain\Segment;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomerDetailsCsvFormatterTest.
 */
class CustomerDetailsCsvFormatterTest extends TestCase
{
    protected $segmentedCustomersRepository;
    protected $customerDetailsRepository;
    protected $levelCustomersRepository;
    protected $accountDetailsProvider;
    protected $segment;
    protected $level;
    protected $account;

    /**
     * {@inheritdoc}
     *
     * @throws \Assert\AssertionFailedException
     */
    protected function setUp(): void
    {
        $customerIdString = '22200000-0000-474c-b092-b0dd880c07e2';
        $this->segmentedCustomersRepository = $this->getMockBuilder(Repository::class)->getMock();
        $this->levelCustomersRepository = $this->getMockBuilder(Repository::class)->getMock();
        $this->customerDetailsRepository = $this->getMockBuilder(CustomerDetailsRepository::class)->getMock();

        $customerDetails = $this->getMockBuilder(CustomerDetails::class)
            ->setMockClassName('CustomerDetails')
            ->disableOriginalConstructor()
            ->getMock();
        $customerDetails->method('getBirthDate')->willReturn(new \DateTime());
        $customerDetails->method('getCreatedAt')->willReturn(new \DateTime());
        $customerDetails->method('getCustomerId')->willReturn(new CustomerId($customerIdString));
        $customerDetails->method('getId')->willReturn($customerIdString);

        $customerId = new AccountCustomerId($customerIdString);

        $account = $this->getMockBuilder(Account::class)
            ->disableOriginalConstructor()
            ->getMock();
        $account->method('getCustomerId')->willReturn($customerId);

        $customersLevel = $this->getMockBuilder(CustomersBelongingToOneLevel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customersLevel->method('getCustomers')->willReturn([['customerId' => $customerIdString]]);

        $levelId = $this->getMockBuilder(LevelId::class)
            ->disableOriginalConstructor()
            ->getMock();
        $levelId->method('__toString')->willReturn('00000000');

        $this->level = $this->getMockBuilder(Level::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->level->method('getLevelId')->willReturn($levelId);

        $segmentId = $this->getMockBuilder(SegmentId::class)
            ->disableOriginalConstructor()
            ->getMock();
        $segmentId->method('__toString')->willReturn('00000');

        $this->segment = $this->getMockBuilder(Segment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->segment->method('getSegmentId')->willReturn($segmentId);

        $this->segmentedCustomersRepository->method('findBy')
            ->with($this->arrayHasKey('segmentId'))
            ->willReturn([$account]);
        $this->levelCustomersRepository->method('findBy')
            ->with($this->arrayHasKey('levelId'))
            ->willReturn([$customersLevel]);
        $this->customerDetailsRepository->method('find')
            ->willReturn($customerDetails);

        // Mock AccountDetails for used points and active points fields
        $this->account = $this->getMockBuilder(AccountDetails::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->account->method('getAvailableAmount')->willReturn(10.0);
        $this->account->method('getUsedAmount')->willReturn(20.0);

        $this->accountDetailsProvider = $this->getMockBuilder(AccountDetailsProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->accountDetailsProvider->method('getAccountByCustomer')->willReturn($this->account);
        $this->accountDetailsProvider->method('getCustomerById')->willReturn(
            $this->getMockBuilder(Customer::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
    }

    /**
     * @test
     */
    public function it_returns_properly_formatted_csv(): void
    {
        $formatter = new CustomerDetailsCsvFormatter(
            $this->segmentedCustomersRepository,
            $this->levelCustomersRepository,
            $this->customerDetailsRepository,
            $this->accountDetailsProvider
        );

        $segment = $formatter->getFormattedSegmentUsers($this->segment);
        $this->assertInternalType('array', $segment);

        $level = $formatter->getFormattedLevelUsers($this->level);
        $this->assertInternalType('array', $level);

        $levelMap = $formatter->getLevelUsersCsvMap();
        $this->assertInternalType('array', $levelMap);
        $this->assertCount(count($levelMap), reset($level));

        $segmentMap = $formatter->getSegmentUsersCsvMap();
        $this->assertInternalType('array', $segmentMap);
        $this->assertCount(count($segmentMap), reset($segment));
    }
}
