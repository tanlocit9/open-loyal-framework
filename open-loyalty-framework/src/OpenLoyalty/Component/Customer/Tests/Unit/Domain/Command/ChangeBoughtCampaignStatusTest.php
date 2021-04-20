<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\Command\ActivateBoughtCampaign;
use OpenLoyalty\Component\Customer\Domain\Command\CancelBoughtCampaign;
use OpenLoyalty\Component\Customer\Domain\Command\ExpireBoughtCampaign;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignStatusWasChanged;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignWasBoughtByCustomer;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasRegistered;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\TransactionId;

/**
 * Class ChangeBoughtCampaignStatusTest.
 */
final class ChangeBoughtCampaignStatusTest extends CustomerCommandHandlerTest
{
    /**
     * @test
     */
    public function it_activate_bought_campaign()
    {
        $campaignId = new CampaignId('00000000-0000-0000-0000-000000000000');
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000001');
        $coupon = new Coupon('123', 'test');

        $campaignWasBoughtByCustomer = new CampaignWasBoughtByCustomer(
            $customerId,
            $campaignId,
            'test',
            99,
            $coupon,
            Campaign::REWARD_TYPE_DISCOUNT_CODE
        );

        $this->scenario
            ->withAggregateId((string) $customerId)
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
                $campaignWasBoughtByCustomer,
            ])
            ->when(new ActivateBoughtCampaign($customerId, $campaignId, $coupon))
            ->then([
                new CampaignStatusWasChanged($customerId, $campaignId, $coupon, CampaignPurchase::STATUS_ACTIVE),
            ]);
    }

    /**
     * @test
     */
    public function it_expire_bought_campaign()
    {
        $campaignId = new CampaignId('00000000-0000-0000-0000-000000000000');
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000001');
        $coupon = new Coupon('123', 'test');

        $campaignWasBoughtByCustomer = new CampaignWasBoughtByCustomer(
            $customerId,
            $campaignId,
            'test',
            99,
            $coupon,
            Campaign::REWARD_TYPE_DISCOUNT_CODE
        );

        $this->scenario
            ->withAggregateId((string) $customerId)
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
                $campaignWasBoughtByCustomer,
            ])
            ->when(new ExpireBoughtCampaign($customerId, $campaignId, $coupon))
            ->then([
                new CampaignStatusWasChanged($customerId, $campaignId, $coupon, CampaignPurchase::STATUS_EXPIRED),
            ]);
    }

    /**
     * @test
     */
    public function it_cancel_bought_campaign()
    {
        $campaignId = new CampaignId('00000000-0000-0000-0000-000000000000');
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000001');
        $transactionId = new TransactionId('00000000-0000-0000-0000-000000000002');
        $coupon = new Coupon('123', 'test');

        $this->scenario
            ->withAggregateId((string) $customerId)
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
                new CampaignWasBoughtByCustomer($customerId, $campaignId, 'test', 99, $coupon, Campaign::REWARD_TYPE_DISCOUNT_CODE),
            ])
            ->when(new CancelBoughtCampaign($customerId, $campaignId, $coupon, $transactionId))
            ->then([
                new CampaignStatusWasChanged($customerId, $campaignId, $coupon, CampaignPurchase::STATUS_CANCELLED, $transactionId),
            ]);
    }
}
