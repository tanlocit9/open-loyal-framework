<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Tests\Unit\Infrastructure\Service;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CustomerId;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Campaign\Infrastructure\Provider\RewardCampaignProviderInterface;
use OpenLoyalty\Component\Campaign\Infrastructure\Service\CampaignRewardRedeemedTemplateParameterCreator;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Infrastructure\Provider\CustomerDetailsProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CampaignRewardRedeemedTemplateParameterCreatorTest.
 */
final class CampaignRewardRedeemedTemplateParameterCreatorTest extends TestCase
{
    /**
     * @var CampaignBought|MockObject
     */
    private $campaignBought;

    /**
     * @var CustomerDetailsProviderInterface|MockObject
     */
    private $customerDetailsProvider;

    /**
     * @var RewardCampaignProviderInterface|MockObject
     */
    private $campaignProvider;

    /**
     * @var CustomerDetails|MockObject
     */
    private $customerDetails;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerDetailsProvider = $this->createMock(CustomerDetailsProviderInterface::class);

        $campaign = $this->createMock(Campaign::class);
        $campaign->expects($this->once())->method('getName')->willReturn('Campaign GIFT');
        $campaign->expects($this->once())->method('getConditionsDescription')->willReturn('Campaign Description');

        $this->campaignProvider = $this->createMock(RewardCampaignProviderInterface::class);
        $this->campaignProvider->expects($this->once())->method('findById')->willReturn($campaign);

        $this->campaignBought = $this->createMock(CampaignBought::class);

        $this
            ->campaignBought
            ->method('getCustomerId')
            ->willReturn(new CustomerId('00000000-0000-0000-0000-000000000000'))
        ;
        $this
            ->campaignBought
            ->method('getCoupon')
            ->willReturn(new Coupon('ABC-123', '00000000-0000-0000-0000-000000000012'))
        ;
        $this
            ->campaignBought
            ->method('getCampaignId')
            ->willReturn(new CampaignId('00000000-0000-0000-0000-000000000001'))
        ;

        $this->customerDetails = $this->createMock(CustomerDetails::class);
        $this->customerDetails->method('getFirstName')->willReturn('Jon');
        $this->customerDetails->method('getLastName')->willReturn('Doe');
        $this->customerDetails->method('getPhone')->willReturn('+123456789');
        $this->customerDetails->method('getEmail')->willReturn('jon.done@oloy.com');
        $this->customerDetails->method('getAddress')->willReturn(null);
    }

    /**
     * @test
     */
    public function it_create_email_parameters(): void
    {
        $this
            ->customerDetailsProvider
            ->expects($this->once())
            ->method('getCustomerDetailsByCustomerId')
            ->willReturn($this->customerDetails)
        ;

        $creator = new CampaignRewardRedeemedTemplateParameterCreator(
            $this->customerDetailsProvider,
            $this->campaignProvider
        );
        $parameters = $creator->parameters($this->campaignBought, 'reward_redeemed.html.twig');

        $this->assertSame('reward_redeemed.html.twig', $parameters->template());
        $this->assertSame('Jon', $parameters->parameters()['customer_name']);
        $this->assertSame('Doe', $parameters->parameters()['customer_last_name']);
        $this->assertSame('+123456789', $parameters->parameters()['customer_phone_number']);
    }
}
