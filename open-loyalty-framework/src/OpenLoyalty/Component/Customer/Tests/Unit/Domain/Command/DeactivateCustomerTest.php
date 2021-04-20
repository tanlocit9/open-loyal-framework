<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Customer\Domain\Command\DeactivateCustomer;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasDeactivated;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasRegistered;

/**
 * Class DeactivateCustomerTest.
 */
class DeactivateCustomerTest extends CustomerCommandHandlerTest
{
    /**
     * @test
     */
    public function it_deactivates_customer()
    {
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000000');
        $this->scenario
            ->withAggregateId((string) $customerId)
            ->given([
                new CustomerWasRegistered($customerId, CustomerCommandHandlerTest::getCustomerData()),
            ])
            ->when(new DeactivateCustomer($customerId))
            ->then([
                new CustomerWasDeactivated($customerId),
            ]);
    }
}
