<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\Command\BuyCustomerCampaign;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignWasBoughtByCustomer;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasRegistered;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;

/**
 * Class BuyCampaignTest.
 */
final class BuyCampaignTest extends CustomerCommandHandlerTest
{
    /**
     * @test
     */
    public function it_buys_campaign()
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');
        $campaignId = new CampaignId('00000000-0000-0000-0000-000000000001');

        $this->scenario
            ->withAggregateId((string) $customerId)
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
            ])
            ->when(
                new BuyCustomerCampaign(
                    $customerId,
                    $campaignId,
                    'test',
                    99,
                    new Coupon('1234', '123'),
                    Campaign::REWARD_TYPE_DISCOUNT_CODE,
                    CampaignPurchase::STATUS_ACTIVE,
                    null,
                    null
                    )
            )
            ->then([
                new CampaignWasBoughtByCustomer($customerId, $campaignId, 'test', 99, new Coupon('1234', '123'), Campaign::REWARD_TYPE_DISCOUNT_CODE),
            ]);
    }

    /**
     * @test
     */
    public function it_buys_campaign_with_inactive_coupon()
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000002');
        $campaignId = new CampaignId('00000000-0000-0000-0000-000000000003');

        $this->scenario
            ->withAggregateId((string) $customerId)
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
            ])
            ->when(
                new BuyCustomerCampaign(
                $customerId,
                $campaignId,
                'test',
                99,
                new Coupon('1234', '123'),
                Campaign::REWARD_TYPE_DISCOUNT_CODE,
                CampaignPurchase::STATUS_INACTIVE,
                null,
                null
            )
            )->then([
                new CampaignWasBoughtByCustomer(
                    $customerId,
                    $campaignId,
                    'test',
                    99,
                    new Coupon('1234', '123'),
                    Campaign::REWARD_TYPE_DISCOUNT_CODE,
                    CampaignPurchase::STATUS_INACTIVE
                ),
            ]);
    }
}
