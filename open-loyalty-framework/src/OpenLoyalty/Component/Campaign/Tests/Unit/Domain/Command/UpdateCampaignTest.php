<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Command\UpdateCampaign;
use OpenLoyalty\Component\Campaign\Domain\LevelId;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;

/**
 * Class UpdateCampaignTest.
 */
class UpdateCampaignTest extends CampaignCommandHandlerTest
{
    /**
     * @test
     */
    public function it_creates_new_campaign()
    {
        $campaignId = new CampaignId('00000000-0000-0000-0000-000000000000');
        $campaign = new Campaign($campaignId);
        $campaign->setName('not updated');
        $campaign->setShortDescription('not updated');
        $this->campaigns[] = $campaign;

        $handler = $this->createCommandHandler();

        $command = new UpdateCampaign($campaignId, [
            'reward' => Campaign::REWARD_TYPE_GIFT_CODE,
            'levels' => [new LevelId('00000000-0000-0000-0000-000000000000')],
            'segments' => [],
            'unlimited' => false,
            'limit' => 10,
            'limitPerUser' => 2,
            'singleCoupon' => false,
            'coupons' => [new Coupon('123')],
            'daysValid' => 0,
            'daysInactive' => 0,
            'campaignActivity' => [
                'allTimeActive' => false,
                'activeFrom' => new \DateTime('2016-01-01'),
                'activeTo' => new \DateTime('2016-01-11'),
            ],
            'campaignVisibility' => [
                'allTimeVisible' => false,
                'visibleFrom' => new \DateTime('2016-02-01'),
                'visibleTo' => new \DateTime('2016-02-11'),
            ],
            'rewardValue' => 99.95,
            'taxPriceValue' => 100.50,
            'tax' => 23,
            'translations' => [
                'en' => [
                    'name' => 'test',
                    'shortDescription' => 'short desc',
                ],
            ],
        ]);
        $handler->handle($command);
        $campaign = $this->inMemoryRepository->byId($campaignId);
        $this->assertNotNull($campaign);
        $this->assertInstanceOf(Campaign::class, $campaign);
        $this->assertEquals('test', $campaign->getName());
        $this->assertEquals('short desc', $campaign->getShortDescription());
        $this->assertEquals(99.95, $campaign->getRewardValue());
        $this->assertEquals(100.50, $campaign->getTaxPriceValue());
        $this->assertEquals(23, $campaign->getTax());
    }
}
