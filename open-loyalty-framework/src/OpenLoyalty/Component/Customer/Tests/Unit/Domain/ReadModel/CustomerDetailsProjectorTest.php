<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\ReadModel;

use Broadway\ReadModel\Projector;
use Broadway\ReadModel\InMemory\InMemoryRepository;
use Broadway\ReadModel\Testing\ProjectorScenarioTestCase;
use Broadway\Repository\Repository;
use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\CustomerRepository;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignWasBoughtByCustomer;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerLevelWasRecalculated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasActivated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasMovedToLevel;
use OpenLoyalty\Component\Customer\Domain\Event\PosWasAssignedToCustomer;
use OpenLoyalty\Component\Customer\Domain\Event\SellerWasAssignedToCustomer;
use OpenLoyalty\Component\Customer\Domain\LevelId;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\PosId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsProjector;
use OpenLoyalty\Component\Customer\Domain\SellerId;
use OpenLoyalty\Component\Customer\Tests\Unit\Domain\Command\CustomerCommandHandlerTest;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerAddressWasUpdated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerCompanyDetailsWereUpdated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerDetailsWereUpdated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerLoyaltyCardNumberWasUpdated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasDeactivated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasRegistered;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Level\Domain\LevelRepository;
use OpenLoyalty\Component\Level\Domain\ReadModel\LevelDetails;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use OpenLoyalty\Component\Level\Domain\LevelId as LevelLevelId;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CustomerDetailsProjectorTest.
 */
final class CustomerDetailsProjectorTest extends ProjectorScenarioTestCase
{
    const TEST_LEVEL_ID = '00000000-2222-0000-0000-000000000111';
    const TEST_LEVEL_NAME = 'Level name 1';

    /**
     * @return LevelDetails
     */
    protected function createTestLevelDetails(): LevelDetails
    {
        $levelDetails = new LevelDetails(new LevelLevelId(self::TEST_LEVEL_ID));
        $levelDetails->setName(self::TEST_LEVEL_NAME);

        return $levelDetails;
    }

    /**
     * {@inheritdoc}
     */
    protected function createProjector(InMemoryRepository $repository): Projector
    {
        /** @var TransactionDetailsRepository|MockObject $transactionDetailsRepo */
        $transactionDetailsRepo = $this->getMockBuilder(TransactionDetailsRepository::class)->getMock();

        /** @var LevelRepository|MockObject $levelRepository */
        $levelRepository = $this->getMockBuilder(LevelRepository::class)->getMock();
        $levelRepository->method('byId')->willReturn($this->createTestLevelDetails());

        /** @var Repository|MockObject $transactionRepository */
        $transactionRepository = $this->getMockBuilder(Repository::class)->getMock();

        /** @var CustomerRepository|MockObject $customerRepository */
        $customerRepository = $this->getMockBuilder(CustomerRepository::class)->disableOriginalConstructor()->getMock();

        return new CustomerDetailsProjector(
            $repository,
            $customerRepository,
            $transactionDetailsRepo,
            $levelRepository,
            $transactionRepository
        );
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_from_empty_level_to_given_level(): void
    {
        $levelId = new LevelId(self::TEST_LEVEL_ID);
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $data = CustomerCommandHandlerTest::getCustomerData();
        $data['levelId'] = $levelId->__toString();
        $data['level'] = [
            'id' => self::TEST_LEVEL_ID,
            'name' => self::TEST_LEVEL_NAME,
        ];

        $this->scenario
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
            ])
            ->when(new CustomerWasMovedToLevel($customerId, $levelId))
            ->then([$this->createBaseReadModel($customerId, $data)]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_when_level_is_assigned_manually(): void
    {
        $levelId = new LevelId(self::TEST_LEVEL_ID);
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $data = CustomerCommandHandlerTest::getCustomerData();
        $data['levelId'] = $levelId->__toString();
        $data['level'] = [
            'id' => self::TEST_LEVEL_ID,
            'name' => self::TEST_LEVEL_NAME,
        ];
        $data['manuallyAssignedLevelId'] = self::TEST_LEVEL_ID;

        $this->scenario
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
            ])
            ->when(new CustomerWasMovedToLevel($customerId, $levelId, null, true))
            ->then([$this->createBaseReadModel($customerId, $data)]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_when_level_is_empty(): void
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $data = CustomerCommandHandlerTest::getCustomerData();

        $this->scenario
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
            ])
            ->when(new CustomerWasMovedToLevel($customerId, null))
            ->then([$this->createBaseReadModel($customerId, $data)]);
    }

    /**
     * @test
     */
    public function it_updates_last_level_recalculation_date(): void
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $data = CustomerCommandHandlerTest::getCustomerData();
        $date = new \DateTime();
        /** @var CustomerDetails $baseReadModel */
        $baseReadModel = $this->createBaseReadModel($customerId, $data);
        $baseReadModel->setLastLevelRecalculation($date);
        $this->scenario
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
            ])
            ->when(new CustomerLevelWasRecalculated($customerId, $date))
            ->then([$baseReadModel]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_when_manually_assigned_level_is_removed(): void
    {
        $levelId = new LevelId(self::TEST_LEVEL_ID);
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $data = CustomerCommandHandlerTest::getCustomerData();
        $data['levelId'] = $levelId->__toString();
        $data['level'] = [
            'id' => self::TEST_LEVEL_ID,
            'name' => self::TEST_LEVEL_NAME,
        ];
        $data['manuallyAssignedLevelId'] = null;

        $this->scenario
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
                new CustomerWasMovedToLevel($customerId, $levelId, null, true),
            ])
            ->when(new CustomerWasMovedToLevel($customerId, $levelId, null, false, true))
            ->then([$this->createBaseReadModel($customerId, $data)]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_register(): void
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $this->scenario->given([])
            ->when(new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()))
            ->then([$this->createBaseReadModel($customerId, CustomerCommandHandlerTest::getCustomerData())]);
    }

    /**
     * @test
     */
    public function it_create_read_model_on_customer_registered_event_with_empty_phone_number(): void
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $customerData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phone' => '',
            'birthDate' => 653011200,
            'createdAt' => 1470646394,
            'updatedAt' => 1470646394,
            'email' => 'customer@example.com',
            'status' => [
                'type' => 'new',
            ],
        ];
        $this->scenario
            ->given([])
            ->when(new CustomerWasRegistered($customerId, $customerData))
            ->then([$this->createBaseReadModel($customerId, $customerData)]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_register_and_properly_sets_agreements(): void
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $data = CustomerCommandHandlerTest::getCustomerData();
        $data['agreement1'] = true;
        $data['agreement2'] = false;
        $data['agreement3'] = true;

        $this->scenario->given([])
            ->when(new CustomerWasRegistered($customerId, $data))
            ->then([$this->createBaseReadModel($customerId, $data)]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_register_and_properly_sets_labels(): void
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $data = CustomerCommandHandlerTest::getCustomerData();
        $data['agreement1'] = true;
        $data['agreement2'] = false;
        $data['agreement3'] = true;
        $data['labels'] = [
            ['key' => 'l1', 'value' => 'v1'],
        ];

        $this->scenario->given([])
            ->when(new CustomerWasRegistered($customerId, $data))
            ->then([$this->createBaseReadModel($customerId, $data)]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_register_and_address_update(): void
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $customerLoyaltyCardNumberWasUpdated = new CustomerLoyaltyCardNumberWasUpdated(
            $customerId,
            CustomerCommandHandlerTest::getCustomerData()['loyaltyCardNumber']
        );
        $data = CustomerCommandHandlerTest::getCustomerData();
        $data['updatedAt'] = $customerLoyaltyCardNumberWasUpdated->getUpdateAt()->getTimestamp();

        $this->scenario->given([])
            ->when(new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()))
            ->when(new CustomerAddressWasUpdated($customerId, CustomerCommandHandlerTest::getCustomerData()['address']))
            ->when(new CustomerCompanyDetailsWereUpdated($customerId, CustomerCommandHandlerTest::getCustomerData()['company']))
            ->when($customerLoyaltyCardNumberWasUpdated)
            ->then([$this->createReadModel($customerId, $data)]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_register_and_deactivate(): void
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $data = CustomerCommandHandlerTest::getCustomerData();
        $data['active'] = false;
        $data['address'] = null;
        $data['loyaltyCardNumber'] = null;
        $data['company'] = null;
        $data['status']['type'] = 'blocked';

        $this->scenario->given([])
            ->when(new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()))
            ->when(new CustomerWasDeactivated($customerId))
            ->then([$this->createReadModel($customerId, $data)]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_register_and_activate(): void
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $data = CustomerCommandHandlerTest::getCustomerData();
        $data['active'] = true;
        $data['address'] = null;
        $data['loyaltyCardNumber'] = null;
        $data['company'] = null;
        $data['status']['type'] = 'active';
        $data['status']['state'] = 'no-card';

        $this->scenario->given([])
                       ->when(new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()))
                       ->when(new CustomerWasDeactivated($customerId))
                        ->when(new CustomerWasActivated($customerId))
                       ->then([$this->createReadModel($customerId, $data)]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_register_and_name_update(): void
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $data = CustomerCommandHandlerTest::getCustomerData();
        $data['firstName'] = 'Jane';
        unset($data['company']);
        unset($data['loyaltyCardNumber']);
        $customerDetailsWereUpdated = new CustomerDetailsWereUpdated($customerId, ['firstName' => 'Jane']);
        $data['updatedAt'] = $customerDetailsWereUpdated->getUpdateAt()->getTimestamp();

        $this->scenario->given([])
            ->when(new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()))
            ->when(new CustomerAddressWasUpdated($customerId, CustomerCommandHandlerTest::getCustomerData()['address']))
            ->when($customerDetailsWereUpdated)
            ->then([$this->createReadModel($customerId, $data)]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_pos_assigned_to_the_customer(): void
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');
        $posId = new PosId('00000000-0000-0000-0000-000000000000');

        $posWasAssignedToCustomer = new PosWasAssignedToCustomer($customerId, $posId);

        $data = CustomerCommandHandlerTest::getCustomerData();
        $data['posId'] = '00000000-0000-0000-0000-000000000000';
        $data['updatedAt'] = $posWasAssignedToCustomer->getUpdateAt()->getTimestamp();

        $this->scenario->given([])
            ->when(new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()))
            ->when($posWasAssignedToCustomer)
            ->then([$this->createBaseReadModel($customerId, $data)]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_seller_assigned_to_the_customer(): void
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');
        $seller = new SellerId('00000000-0000-0000-0000-000000000000');

        $sellerWasAssignedToCustomer = new SellerWasAssignedToCustomer($customerId, $seller);

        $data = CustomerCommandHandlerTest::getCustomerData();
        $data['sellerId'] = '00000000-0000-0000-0000-000000000000';
        $data['updatedAt'] = $sellerWasAssignedToCustomer->getUpdateAt()->getTimestamp();

        $this->scenario->given([])
                       ->when(new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()))
                       ->when($sellerWasAssignedToCustomer)
                       ->then([$this->createBaseReadModel($customerId, $data)]);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model_on_campaign_bought(): void
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');

        $campaignWasBoughtByCustomer = new CampaignWasBoughtByCustomer(
            $customerId,
            new CampaignId('00000000-0000-0000-0000-000000000000'),
            'campaignName',
            0,
            new Coupon('123', 'couponCode'),
            'reward'
        );

        $data = CustomerCommandHandlerTest::getCustomerData();

        $expected = $this->createBaseReadModel($customerId, $data);
        $expected->addCampaignPurchase(
            new CampaignPurchase(
                $campaignWasBoughtByCustomer->getCreatedAt(),
                $campaignWasBoughtByCustomer->getCostInPoints(),
                $campaignWasBoughtByCustomer->getCampaignId(),
                $campaignWasBoughtByCustomer->getCoupon(),
                $campaignWasBoughtByCustomer->getReward(),
                $campaignWasBoughtByCustomer->getStatus(),
                $campaignWasBoughtByCustomer->getActiveSince(),
                $campaignWasBoughtByCustomer->getActiveTo(),
                $campaignWasBoughtByCustomer->getTransactionId()
            )
        );

        $this->scenario->given([])
                       ->when(new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()))
                       ->when($campaignWasBoughtByCustomer)
                       ->then([$expected]);
    }

    /**
     * @param CustomerId $customerId
     * @param array      $data
     *
     * @return CustomerDetails
     */
    private function createBaseReadModel(CustomerId $customerId, array $data): CustomerDetails
    {
        $data['id'] = (string) $customerId;
        unset($data['loyaltyCardNumber']);
        unset($data['company']);
        unset($data['address']);

        return CustomerDetails::deserialize($data);
    }

    /**
     * @param CustomerId $customerId
     * @param array      $data
     *
     * @return CustomerDetails
     */
    private function createReadModel(CustomerId $customerId, array $data): CustomerDetails
    {
        $data['id'] = (string) $customerId;

        return CustomerDetails::deserialize($data);
    }
}
