<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Tests\Unit\Domain;

use OpenLoyalty\Component\Campaign\Domain\DeliveryStatus;
use OpenLoyalty\Component\Campaign\Domain\Exception\DeliveryStatusException;
use PHPUnit\Framework\TestCase;

/**
 * Class CampaignPurchaseDeliveryStatusTest.
 */
final class DeliveryStatusTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_an_exception_when_status_does_not_exists(): void
    {
        $this->expectException(DeliveryStatusException::class);
        new DeliveryStatus('non-exists-status');
    }

    /**
     * @test
     */
    public function it_return_status_value_when_object_is_converted_to_string(): void
    {
        $status = new DeliveryStatus('canceled');
        $this->assertSame('canceled', (string) $status);
    }

    /**
     * @test
     */
    public function it_returns_default_status_on_object_create(): void
    {
        $status = new DeliveryStatus();
        $this->assertSame('', (string) $status);
    }
}
