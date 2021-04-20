<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Infrastructure\SystemEvent\Listener;

use Broadway\CommandHandling\CommandBus;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBoughtRepository;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignShippingAddress;
use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\Command\ReturnCustomerCampaign;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Infrastructure\SystemEvent\Listener\CreateCouponsReturnListener;
use OpenLoyalty\Component\Transaction\Domain\CustomerId;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\CustomerAssignedToTransactionSystemEvent;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;
use OpenLoyalty\Component\Campaign\Domain\TransactionId as CampaignTransactionId;
use OpenLoyalty\Component\Campaign\Domain\CampaignId as CampaignDomainId;
use OpenLoyalty\Component\Campaign\Domain\CustomerId as CampaignCustomerId;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon as CampaignCoupon;
use OpenLoyalty\Component\Customer\Domain\CustomerId as CustomerDomainId;
use OpenLoyalty\Component\Customer\Domain\TransactionId as CustomerTransactionId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CreateCouponsReturnListenerTest.
 */
final class CreateCouponsReturnListenerTest extends TestCase
{
    private const ID = '00000000-0000-0000-0000-000000000000';

    /**
     * @test
     */
    public function it_does_nothing_when_no_coupons_were_used(): void
    {
        /** @var Campaign|MockObject $campaign */
        $campaign = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->getMock();
        $campaign->method('getReward')->willReturn(Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE);
        $campaign->method('getName')->willReturn('Test campaign');
        $commandBus = $this->getCommandBus();

        $listener = new CreateCouponsReturnListener(
            $this->getTransactionRepository(100, 100),
            $this->getCampaignBoughtRepository([]),
            $commandBus,
            $this->getCampaignRepository($campaign),
            $this->getUuidGenerator()
        );

        $listener->handleCustomerAssignedToTransaction(
            new CustomerAssignedToTransactionSystemEvent(
                new TransactionId(self::ID),
                new CustomerId(self::ID),
                100,
                100,
                '123',
                0,
                null,
                true
            )
        );
        $commandBus->expects($this->never())->method('dispatch');
    }

    /**
     * @test
     */
    public function it_creates_coupon_for_whole_return_single_coupon(): void
    {
        /** @var Campaign|MockObject $campaign */
        $campaign = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->getMock();
        $campaign->method('getReward')->willReturn(Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE);
        $campaign->method('getName')->willReturn('Test campaign');
        $commandBus = $this->getCommandBus();

        /** @var MockObject|CampaignShippingAddress $campaignShippingAddress */
        $campaignShippingAddress = $this->getMockBuilder(CampaignShippingAddress::class)
                                        ->disableOriginalConstructor()->getMock();

        $activeTo = new \DateTime('+10 days');
        $bought = [
            new CampaignBought(
                new CampaignDomainId(self::ID),
                new CampaignCustomerId(self::ID),
                new \DateTime('-1 month'),
                new CampaignCoupon('10'),
                Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
                'Test campaign',
                'test@example.com',
                '123',
                $campaignShippingAddress,
                CampaignPurchase::STATUS_ACTIVE,
                false,
                null,
                null,
                null,
                null,
                null,
                null,
                $activeTo,
                new CampaignTransactionId(self::ID)
            ),
        ];

        $listener = new CreateCouponsReturnListener(
            $this->getTransactionRepository(100, 100),
            $this->getCampaignBoughtRepository($bought),
            $commandBus,
            $this->getCampaignRepository($campaign),
            $this->getUuidGenerator()
        );

        $commandBus->expects($this->once())->method('dispatch')->with($this->equalTo(
            new ReturnCustomerCampaign(
                new CustomerDomainId(self::ID),
                new CampaignId(self::ID),
                'Test campaign',
                0,
                new Coupon('123', '10'),
                Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
                new CustomerTransactionId(self::ID),
                '00000000-0000-0000-0000-000000000000_00000000-0000-0000-0000-000000000000_10_00000000-0000-0000-0000-000000000000',
                CampaignPurchase::STATUS_ACTIVE,
                null,
                $activeTo
            )
        ));

        $listener->handleCustomerAssignedToTransaction(
            new CustomerAssignedToTransactionSystemEvent(
                new TransactionId(self::ID),
                new CustomerId(self::ID),
                100,
                100,
                '123',
                0,
                null,
                true
            )
        );
    }

    /**
     * @test
     */
    public function it_creates_coupon_for_whole_return_multiple_coupons(): void
    {
        /** @var Campaign|MockObject $campaign */
        $campaign = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->getMock();
        $campaign->method('getReward')->willReturn(Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE);
        $campaign->method('getName')->willReturn('Test campaign');
        $commandBus = $this->getCommandBus();

        $activeTo1 = new \DateTime('+10 days');
        $activeTo2 = new \DateTime('+12 days');

        /** @var MockObject|CampaignShippingAddress $campaignShippingAddress */
        $campaignShippingAddress = $this->getMockBuilder(CampaignShippingAddress::class)
                                        ->disableOriginalConstructor()->getMock();

        $bought = [
            new CampaignBought(
                new CampaignDomainId(self::ID),
                new CampaignCustomerId(self::ID),
                new \DateTime('-1 month'),
                new CampaignCoupon('10'),
                Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
                'Test campaign',
                'test@example.com',
                '123',
                $campaignShippingAddress,
                CampaignPurchase::STATUS_ACTIVE,
                false,
                null,
                null,
                null,
                null,
                null,
                null,
                $activeTo1,
                new CampaignTransactionId(self::ID)
            ),
            new CampaignBought(
                new CampaignDomainId(self::ID),
                new CampaignCustomerId(self::ID),
                new \DateTime('-1 month'),
                new CampaignCoupon('20'),
                Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
                'Test campaign',
                'test@example.com',
                '123',
                $campaignShippingAddress,
                CampaignPurchase::STATUS_ACTIVE,
                false,
                null,
                null,
                null,
                null,
                null,
                null,
                $activeTo2,
                new CampaignTransactionId(self::ID)
            ),
        ];

        $listener = new CreateCouponsReturnListener(
            $this->getTransactionRepository(100, 100),
            $this->getCampaignBoughtRepository($bought),
            $commandBus,
            $this->getCampaignRepository($campaign),
            $this->getUuidGenerator()
        );

        $commandBus->expects($this->at(0))->method('dispatch')->with(
            $this->equalTo(
                new ReturnCustomerCampaign(
                    new CustomerDomainId(self::ID),
                    new CampaignId(self::ID),
                    'Test campaign',
                    0,
                    new Coupon('123', '20'),
                    Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
                    new CustomerTransactionId(self::ID),
                    '00000000-0000-0000-0000-000000000000_00000000-0000-0000-0000-000000000000_20_00000000-0000-0000-0000-000000000000',
                    CampaignPurchase::STATUS_ACTIVE,
                    null,
                    $activeTo2
                )
            )
        );
        $commandBus->expects($this->at(1))->method('dispatch')->with(
            $this->equalTo(
                new ReturnCustomerCampaign(
                    new CustomerDomainId(self::ID),
                    new CampaignId(self::ID),
                    'Test campaign',
                    0,
                    new Coupon('123', '10'),
                    Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
                    new CustomerTransactionId(self::ID),
                    '00000000-0000-0000-0000-000000000000_00000000-0000-0000-0000-000000000000_10_00000000-0000-0000-0000-000000000000',
                    CampaignPurchase::STATUS_ACTIVE,
                    null,
                    $activeTo1
                )
            )
        );

        $listener->handleCustomerAssignedToTransaction(
            new CustomerAssignedToTransactionSystemEvent(
                new TransactionId(self::ID),
                new CustomerId(self::ID),
                100,
                100,
                '123',
                0,
                null,
                true
            )
        );
    }

    /**
     * @test
     */
    public function it_creates_coupon_for_part_return_single_coupon(): void
    {
        /** @var Campaign|MockObject $campaign */
        $campaign = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->getMock();
        $campaign->method('getReward')->willReturn(Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE);
        $campaign->method('getName')->willReturn('Test campaign');
        $commandBus = $this->getCommandBus();

        /** @var MockObject|CampaignShippingAddress $campaignShippingAddress */
        $campaignShippingAddress = $this->getMockBuilder(CampaignShippingAddress::class)
                                        ->disableOriginalConstructor()->getMock();

        $activeTo = new \DateTime('+10 days');
        $bought = [
            new CampaignBought(
                new CampaignDomainId(self::ID),
                new CampaignCustomerId(self::ID),
                new \DateTime('-1 month'),
                new CampaignCoupon('10'),
                Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
                'Test campaign',
                'test@example.com',
                '123',
                $campaignShippingAddress,
                CampaignPurchase::STATUS_ACTIVE,
                false,
                null,
                null,
                null,
                null,
                null,
                null,
                $activeTo,
                new CampaignTransactionId(self::ID)
            ),
        ];

        $listener = new CreateCouponsReturnListener(
            $this->getTransactionRepository(50, 100),
            $this->getCampaignBoughtRepository($bought),
            $commandBus,
            $this->getCampaignRepository($campaign),
            $this->getUuidGenerator()
        );

        $commandBus->expects($this->once())->method('dispatch')->with($this->equalTo(
            new ReturnCustomerCampaign(
                new CustomerDomainId(self::ID),
                new CampaignId(self::ID),
                'Test campaign',
                0,
                new Coupon('123', '5'),
                Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
                new CustomerTransactionId(self::ID),
                '00000000-0000-0000-0000-000000000000_00000000-0000-0000-0000-000000000000_10_00000000-0000-0000-0000-000000000000',
                CampaignPurchase::STATUS_ACTIVE,
                null,
                $activeTo
            )
        ));

        $listener->handleCustomerAssignedToTransaction(
            new CustomerAssignedToTransactionSystemEvent(
                new TransactionId(self::ID),
                new CustomerId(self::ID),
                50,
                50,
                '123',
                0,
                null,
                true
            )
        );
    }

    /**
     * @test
     */
    public function it_creates_coupon_for_part_return_multiple_coupons(): void
    {
        /** @var Campaign|MockObject $campaign */
        $campaign = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->getMock();
        $campaign->method('getReward')->willReturn(Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE);
        $campaign->method('getName')->willReturn('Test campaign');
        $commandBus = $this->getCommandBus();

        /** @var MockObject|CampaignShippingAddress $campaignShippingAddress */
        $campaignShippingAddress = $this->getMockBuilder(CampaignShippingAddress::class)
                                        ->disableOriginalConstructor()->getMock();

        $activeTo1 = new \DateTime('+10 days');
        $activeTo2 = new \DateTime('+12 days');
        $bought = [
            new CampaignBought(
                new CampaignDomainId(self::ID),
                new CampaignCustomerId(self::ID),
                new \DateTime('-1 month'),
                new CampaignCoupon('10'),
                Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
                'Test campaign',
                'test@example.com',
                '123',
                $campaignShippingAddress,
                CampaignPurchase::STATUS_ACTIVE,
                false,
                null,
                null,
                null,
                null,
                null,
                null,
                $activeTo1,
                new CampaignTransactionId(self::ID)
            ),
            new CampaignBought(
                new CampaignDomainId(self::ID),
                new CampaignCustomerId(self::ID),
                new \DateTime('-1 month'),
                new CampaignCoupon('20'),
                Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
                'Test campaign',
                'test@example.com',
                '123',
                $campaignShippingAddress,
                CampaignPurchase::STATUS_ACTIVE,
                false,
                null,
                null,
                null,
                null,
                null,
                null,
                $activeTo2,
                new CampaignTransactionId(self::ID)
            ),
        ];

        $listener = new CreateCouponsReturnListener(
            $this->getTransactionRepository(85, 100),
            $this->getCampaignBoughtRepository($bought),
            $commandBus,
            $this->getCampaignRepository($campaign),
            $this->getUuidGenerator()
        );

        $commandBus->expects($this->at(0))->method('dispatch')->with(
            $this->equalTo(
                new ReturnCustomerCampaign(
                    new CustomerDomainId(self::ID),
                    new CampaignId(self::ID),
                    'Test campaign',
                    0,
                    new Coupon('123', '20'),
                    Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
                    new CustomerTransactionId(self::ID),
                    '00000000-0000-0000-0000-000000000000_00000000-0000-0000-0000-000000000000_20_00000000-0000-0000-0000-000000000000',
                    CampaignPurchase::STATUS_ACTIVE,
                    null,
                    $activeTo2
                )
            )
        );
        $commandBus->expects($this->at(1))->method('dispatch')->with(
            $this->equalTo(
                new ReturnCustomerCampaign(
                    new CustomerDomainId(self::ID),
                    new CampaignId(self::ID),
                    'Test campaign',
                    0,
                    new Coupon('123', '5.5'),
                    Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
                    new CustomerTransactionId(self::ID),
                    '00000000-0000-0000-0000-000000000000_00000000-0000-0000-0000-000000000000_10_00000000-0000-0000-0000-000000000000',
                    CampaignPurchase::STATUS_ACTIVE,
                    null,
                    $activeTo1
                )
            )
        );

        $listener->handleCustomerAssignedToTransaction(
            new CustomerAssignedToTransactionSystemEvent(
                new TransactionId(self::ID),
                new CustomerId(self::ID),
                50,
                50,
                '123',
                0,
                null,
                true
            )
        );
    }

    /**
     * @test
     */
    public function it_does_nothing_when_coupon_already_returned(): void
    {
        /** @var Campaign|MockObject $campaign */
        $campaign = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->getMock();
        $campaign->method('getReward')->willReturn(Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE);
        $campaign->method('getName')->willReturn('Test campaign');
        $commandBus = $this->getCommandBus();

        /** @var MockObject|CampaignShippingAddress $campaignShippingAddress */
        $campaignShippingAddress = $this->getMockBuilder(CampaignShippingAddress::class)
                                        ->disableOriginalConstructor()->getMock();

        $activeTo = new \DateTime('+10 days');
        $campaignBought = new CampaignBought(
            new CampaignDomainId(self::ID),
            new CampaignCustomerId(self::ID),
            new \DateTime('-1 month'),
            new CampaignCoupon('10'),
            Campaign::REWARD_TYPE_PERCENTAGE_DISCOUNT_CODE,
            'Test campaign',
            'test@example.com',
            '123',
            $campaignShippingAddress,
            CampaignPurchase::STATUS_ACTIVE,
            false,
            null,
            null,
            null,
            null,
            null,
            null,
            $activeTo,
            new CampaignTransactionId(self::ID)
        );
        $campaignBought->setReturnedAmount(10);
        $bought = [
            $campaignBought,
        ];

        $listener = new CreateCouponsReturnListener(
            $this->getTransactionRepository(100, 100),
            $this->getCampaignBoughtRepository($bought),
            $commandBus,
            $this->getCampaignRepository($campaign),
            $this->getUuidGenerator()
        );

        $listener->handleCustomerAssignedToTransaction(
            new CustomerAssignedToTransactionSystemEvent(
                new TransactionId(self::ID),
                new CustomerId(self::ID),
                100,
                100,
                '123',
                0,
                null,
                true
            )
        );
        $commandBus->expects($this->never())->method('dispatch');
    }

    /**
     * @param Campaign $campaign
     *
     * @return MockObject|CampaignRepository
     */
    private function getCampaignRepository(Campaign $campaign)
    {
        $repo = $this->getMockBuilder(CampaignRepository::class)->getMock();
        $repo->method('byId')->willReturn($campaign);

        return $repo;
    }

    /**
     * @return MockObject|CommandBus
     */
    private function getCommandBus()
    {
        return $this->getMockBuilder(CommandBus::class)->getMock();
    }

    /**
     * @param array $purchases
     *
     * @return MockObject|CampaignBoughtRepository
     */
    private function getCampaignBoughtRepository(array $purchases)
    {
        $repo = $this->getMockBuilder(CampaignBoughtRepository::class)->disableOriginalConstructor()->getMock();
        $repo->method('findByCustomerIdAndUsedForTransactionId')->willReturn($purchases);

        return $repo;
    }

    /**
     * @param float $returnedAmount
     * @param float $revisedAmount
     *
     * @return MockObject|TransactionDetailsRepository
     */
    private function getTransactionRepository(float $returnedAmount, float $revisedAmount)
    {
        $transaction1 = $this->getMockBuilder(TransactionDetails::class)
            ->disableOriginalConstructor()
            ->getMock();
        $transaction1->method('getGrossValue')->willReturn($returnedAmount);
        $transaction1->method('getId')->willReturn(self::ID);
        $transaction1->method('getDocumentType')->willReturn(Transaction::TYPE_RETURN);
        $transaction1->method('getRevisedDocument')->willReturn('123');

        $transaction2 = $this->getMockBuilder(TransactionDetails::class)
            ->disableOriginalConstructor()
            ->getMock();
        $transaction2->method('getGrossValue')->willReturn($revisedAmount);

        $repo = $this->getMockBuilder(TransactionDetailsRepository::class)
            ->disableOriginalConstructor()->getMock();
        $repo->method('find')->willReturn($transaction1);
        $repo->method('findBy')->willReturn([$transaction2]);

        return $repo;
    }

    /**
     * @return MockObject|UuidGeneratorInterface
     */
    private function getUuidGenerator(): MockObject
    {
        $mock = $this->getMockBuilder(UuidGeneratorInterface::class)->getMock();
        $mock->method('generate')->willReturn('123');

        return $mock;
    }
}
