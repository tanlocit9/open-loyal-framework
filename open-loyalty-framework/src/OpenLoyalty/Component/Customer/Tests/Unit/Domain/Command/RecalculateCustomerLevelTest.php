<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Customer\Domain\Command\RecalculateCustomerLevel;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerLevelWasRecalculated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasRegistered;
use OpenLoyalty\Component\Customer\Domain\PosId;

/**
 * Class RecalculateCustomerLevelTest.
 */
final class RecalculateCustomerLevelTest extends CustomerCommandHandlerTest
{
    /**
     * @test
     */
    public function it_recalculates_level()
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');
        $posId = new PosId('00000000-0000-0000-0000-000000000011');
        $date = new \DateTime();
        $this->scenario
            ->withAggregateId((string) $customerId)
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
            ])
            ->when(new RecalculateCustomerLevel($customerId, $date))
            ->then([
                new CustomerLevelWasRecalculated($customerId, $date),
            ]);
    }
}
