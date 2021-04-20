<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Tests\Unit\Infrastructure\EventListener;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBoughtRepository;
use OpenLoyalty\Component\Campaign\Infrastructure\EventListener\CampaignRewardRedeemedSendEmailListener;
use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CampaignUsageWasChangedSystemEvent;
use OpenLoyalty\Component\Customer\Domain\TransactionId;
use OpenLoyalty\Component\Campaign\Infrastructure\Service\CampaignRewardRedeemedEmailSenderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CampaignRewardRedeemedSendEmailListenerTest.
 */
final class CampaignRewardRedeemedSendEmailListenerTest extends TestCase
{
    /**
     * @var CampaignRewardRedeemedEmailSenderInterface|MockObject
     */
    private $sender;

    /**
     * @var CampaignBoughtRepository|MockObject
     */
    private $repository;

    /**
     * @var CampaignRepository|MockObject
     */
    private $campaignRepository;

    /**
     * @var CampaignId
     */
    private $campaignId;

    /**
     * @var CustomerId
     */
    private $customerId;

    /**
     * @var TransactionId
     */
    private $transactionId;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->sender = $this->createMock(CampaignRewardRedeemedEmailSenderInterface::class);
        $this->repository = $this->createMock(CampaignBoughtRepository::class);
        $this->campaignRepository = $this->createMock(CampaignRepository::class);

        $this->coupon = new Coupon('00000000-0000-0000-0000-000000000004', 'WER-123');
        $this->campaignId = new CampaignId('00000000-0000-0000-0000-000000000001');
        $this->customerId = new CustomerId('00000000-0000-0000-0000-000000000002');
        $this->transactionId = new TransactionId('00000000-0000-0000-0000-000000000003');
    }

    /**
     * @test
     */
    public function it_sends_email_on_event_when_coupon_is_marked_as_used(): void
    {
        $campaignBought = $this->createMock(CampaignBought::class);
        $this->repository->expects($this->once())->method('findOneByCouponId')->willReturn($campaignBought);

        $campaign = $this->createMock(Campaign::class);
        $campaign->expects($this->once())->method('isFulfillmentTracking')->willReturn(true);

        $this->campaignRepository->expects($this->once())->method('byId')->willReturn($campaign);

        $this->sender->expects($this->once())->method('send');

        $listener = new CampaignRewardRedeemedSendEmailListener($this->sender, $this->repository, $this->campaignRepository);

        $payload = [
            'customerId' => (string) $this->customerId,
            'campaignId' => (string) $this->campaignId,
            'couponId' => (string) $this->coupon->getId(),
            'couponCode' => $this->coupon->getCode(),
            'used' => true,
            'transactionId' => null,
        ];

        $listener->__invoke(new CampaignUsageWasChangedSystemEvent(
            $this->customerId,
            $this->campaignId,
            $this->coupon,
            $this->transactionId,
            true
        ));
    }

    /**
     * @test
     */
    public function it_sends_email_on_event_when_coupon_is_marked_as_used_with_transaction_id(): void
    {
        $campaignBought = $this->createMock(CampaignBought::class);
        $this->repository->expects($this->once())->method('findOneByCouponId')->willReturn($campaignBought);

        $this->sender->expects($this->once())->method('send');

        $campaign = $this->createMock(Campaign::class);
        $campaign->expects($this->once())->method('isFulfillmentTracking')->willReturn(true);

        $this->campaignRepository->expects($this->once())->method('byId')->willReturn($campaign);

        $listener = new CampaignRewardRedeemedSendEmailListener($this->sender, $this->repository, $this->campaignRepository);

        $payload = [
            'customerId' => (string) $this->customerId,
            'campaignId' => (string) $this->campaignId,
            'couponId' => (string) $this->coupon->getId(),
            'couponCode' => $this->coupon->getCode(),
            'used' => true,
            'transactionId' => (string) $this->transactionId,
        ];

        $listener->__invoke(new CampaignUsageWasChangedSystemEvent(
            $this->customerId,
            $this->campaignId,
            $this->coupon,
            $this->transactionId,
            true
        ));
    }

    /**
     * @test
     */
    public function it_not_sends_email_on_event_when_coupon_is_not_marked_as_used(): void
    {
        $this->campaignRepository->expects($this->never())->method('byId');
        $this->sender->expects($this->never())->method('send');
        $this->repository->expects($this->never())->method('findOneByCouponId');

        $listener = new CampaignRewardRedeemedSendEmailListener($this->sender, $this->repository, $this->campaignRepository);

        $payload = [
            'customerId' => (string) $this->customerId,
            'campaignId' => (string) $this->campaignId,
            'couponId' => (string) $this->coupon->getId(),
            'couponCode' => $this->coupon->getCode(),
            'used' => false,
            'transactionId' => (string) $this->transactionId,
        ];

        $listener->__invoke(new CampaignUsageWasChangedSystemEvent(
            $this->customerId,
            $this->campaignId,
            $this->coupon,
            $this->transactionId,
            false
        ));
    }

    /**
     * @test
     */
    public function it_not_sends_email_on_event_when_campaign_fulfillment_tracking_process_is_false(): void
    {
        $campaign = $this->createMock(Campaign::class);
        $campaign->expects($this->once())->method('isFulfillmentTracking')->willReturn(false);
        $this->campaignRepository->expects($this->once())->method('byId')->willReturn($campaign);

        $this->sender->expects($this->never())->method('send');
        $this->repository->expects($this->never())->method('findOneByCouponId');

        $listener = new CampaignRewardRedeemedSendEmailListener($this->sender, $this->repository, $this->campaignRepository);

        $payload = [
            'customerId' => (string) $this->customerId,
            'campaignId' => (string) $this->campaignId,
            'couponId' => (string) $this->coupon->getId(),
            'couponCode' => $this->coupon->getCode(),
            'used' => true,
            'transactionId' => (string) $this->transactionId,
        ];

        $listener->__invoke(new CampaignUsageWasChangedSystemEvent(
            $this->customerId,
            $this->campaignId,
            $this->coupon,
            $this->transactionId,
            true
        ));
    }
}
