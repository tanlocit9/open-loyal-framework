<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Unit\Domain;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Provider\CouponActivationDateProvider;
use PHPUnit\Framework\TestCase;

class CouponActivationDateProviderTest extends TestCase
{
    /**
     * @var CouponActivationDateProvider
     */
    private $activationDateService;

    public function setUp()
    {
        $this->activationDateService = new CouponActivationDateProvider();
    }

    /**
     * @test
     */
    public function it_should_return_activation_date()
    {
        $campaign = new Campaign(new CampaignId('3a40b784-913f-45ee-8646-a78b2b4f5cef'));
        $campaign->setDaysInactive(30);

        $now = new \DateTime('2018-07-01');
        $activationDate = $this->activationDateService->getActivationDate($campaign, $now);
        $this->assertEquals('2018-07-31', $activationDate->format('Y-m-d'));
    }
}
