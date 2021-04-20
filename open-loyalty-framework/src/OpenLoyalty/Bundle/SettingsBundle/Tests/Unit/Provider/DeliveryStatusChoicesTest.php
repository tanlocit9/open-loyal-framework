<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\SettingsBundle\Tests\Unit\Provider;

use OpenLoyalty\Bundle\SettingsBundle\Provider\DeliveryStatusChoices;
use PHPUnit\Framework\TestCase;

/**
 * Class DeliveryStatusChoicesTest.
 */
final class DeliveryStatusChoicesTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_array_with_statuses(): void
    {
        $status = new DeliveryStatusChoices();
        $this->assertSame(['choices' => ['ordered', 'shipped', 'delivered', 'canceled']], $status->getChoices());
    }

    /**
     * @test
     */
    public function it_returns_delivery_status_on_type(): void
    {
        $status = new DeliveryStatusChoices();
        $this->assertSame('deliveryStatus', $status->getType());
    }
}
