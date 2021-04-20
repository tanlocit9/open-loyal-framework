<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateBoughtCampaignCouponCommand;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignCouponWasChanged;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignWasBoughtByCustomer;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasRegistered;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\TransactionId;

/**
 * Class ChangeBoughtCampaignStatusTest.
 */
final class UpdateBoughtCampaignCouponTest extends CustomerCommandHandlerTest
{
    /**
     * @test
     */
    public function it_update_bought_campaign_coupon()
    {
        $campaignId = new CampaignId('00000000-0000-0000-0000-000000000000');
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000001');
        $transactionId = new TransactionId('00000000-0000-0000-0000-000000000002');
        $createdAt = new \DateTime();
        $coupon = new Coupon('123', 'test');
        $newCoupon = new Coupon('234', 'test2');

        $this->scenario
            ->withAggregateId((string) $customerId)
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
                new CampaignWasBoughtByCustomer($customerId, $campaignId, 'test', 99, $coupon, Campaign::REWARD_TYPE_DISCOUNT_CODE),
            ])
            ->when(
                new UpdateBoughtCampaignCouponCommand(
                    (string) $customerId,
                    (string) $campaignId,
                    (string) $transactionId,
                    $createdAt,
                    $newCoupon->getCode(),
                    $newCoupon->getId()
                )
            )
            ->then([
                new CampaignCouponWasChanged(
                    $customerId,
                    $campaignId,
                    $transactionId,
                    $createdAt,
                    $newCoupon
                ),
            ]);
    }
}
