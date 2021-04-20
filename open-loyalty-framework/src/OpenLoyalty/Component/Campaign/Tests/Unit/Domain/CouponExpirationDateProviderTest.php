<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Unit\Domain;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Provider\CouponExpirationDateProvider;
use PHPUnit\Framework\TestCase;

class CouponExpirationDateProviderTest extends TestCase
{
    /**
     * @var CouponExpirationDateProvider
     */
    private $activationDateService;

    public function setUp()
    {
        $this->activationDateService = new CouponExpirationDateProvider();
    }

    /**
     * @test
     */
    public function it_should_return_expiration_date()
    {
        $campaign = new Campaign(new CampaignId('3a40b784-913f-45ee-8646-a78b2b4f5cef'));
        $campaign->setDaysValid(30);

        $now = new \DateTime('2018-07-01');
        $expirationDate = $this->activationDateService->getExpirationDate($campaign, $now);
        $this->assertEquals('2018-07-31', $expirationDate->format('Y-m-d'));
    }
}
