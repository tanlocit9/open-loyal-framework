<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\Event;

use Broadway\Serializer\Testing\SerializableEventTestCase;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerAddressWasUpdated;
use OpenLoyalty\Component\Customer\Tests\Unit\Domain\Command\CustomerCommandHandlerTest;
use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class CustomerAddressWasUpdatedTest.
 */
final class CustomerAddressWasUpdatedTest extends SerializableEventTestCase
{
    const USER_ID = '00000000-0000-0000-0000-000000000000';

    public function getters_of_event_works()
    {
        $event = $this->createEvent();

        $this->assertEquals(static::USER_ID, $event->getCustomerId());
        $this->assertEquals(CustomerCommandHandlerTest::getCustomerData()['address'], $event->getAddressData());
    }

    /**
     * @return CustomerAddressWasUpdated
     */
    protected function createEvent()
    {
        return new CustomerAddressWasUpdated(new CustomerId(static::USER_ID), CustomerCommandHandlerTest::getCustomerData()['address']);
    }
}
