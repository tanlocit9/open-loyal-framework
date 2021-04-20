<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\EarningRule\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\EarningRule\Domain\Command\CreateEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleGeo;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\EarningRule\Domain\EventEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\InstantRewardRule;
use OpenLoyalty\Component\EarningRule\Domain\PointsEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\ProductPurchaseEarningRule;

/**
 * Class CreateEarningRuleTest.
 */
class CreateEarningRuleTest extends EarningRuleCommandHandlerAbstract
{
    /**
     * @test
     */
    public function it_creates_new_geo_earning_rule()
    {
        $handler = $this->createCommandHandler();
        $ruleId = new EarningRuleId('00000000-0000-0000-0000-000000000000');

        $command = new CreateEarningRule($ruleId, EarningRule::TYPE_GEOLOCATION, [
            'name' => 'test',
            'description' => 'desc',
            'startAt' => (new \DateTime())->getTimestamp(),
            'endAt' => (new \DateTime('+1 month'))->getTimestamp(),
            'radius' => 100.00,
            'latitude' => 10.23,
            'longitude' => 123.99,
            'pointsAmount' => 89.00,
        ]);
        $handler->handle($command);
        $rule = $this->inMemoryRepository->byId($ruleId);
        $this->assertNotNull($rule);
        $this->assertInstanceOf(EarningRuleGeo::class, $rule);
    }

    /**
     * @test
     */
    public function it_creates_new_event_earning_rule()
    {
        $handler = $this->createCommandHandler();
        $ruleId = new EarningRuleId('00000000-0000-0000-0000-000000000000');

        $command = new CreateEarningRule($ruleId, EarningRule::TYPE_EVENT, [
            'name' => 'test',
            'description' => 'desc',
            'startAt' => (new \DateTime())->getTimestamp(),
            'endAt' => (new \DateTime('+1 month'))->getTimestamp(),
            'eventName' => 'event',
            'pointsAmount' => 100,
        ]);
        $handler->handle($command);
        $rule = $this->inMemoryRepository->byId($ruleId);
        $this->assertNotNull($rule);
        $this->assertInstanceOf(EventEarningRule::class, $rule);
    }

    /**
     * @test
     * @expectedException \Assert\InvalidArgumentException
     */
    public function it_throws_exception_on_empty_name()
    {
        $handler = $this->createCommandHandler();
        $ruleId = new EarningRuleId('00000000-0000-0000-0000-000000000000');

        $command = new CreateEarningRule($ruleId, EarningRule::TYPE_EVENT, [
            'description' => 'desc',
            'startAt' => (new \DateTime())->getTimestamp(),
            'endAt' => (new \DateTime('+1 month'))->getTimestamp(),
            'eventName' => 'event',
            'pointsAmount' => 100,
        ]);
        $handler->handle($command);
    }

    /**
     * @test
     */
    public function it_creates_new_points_earning_rule()
    {
        $handler = $this->createCommandHandler();
        $ruleId = new EarningRuleId('00000000-0000-0000-0000-000000000000');

        $command = new CreateEarningRule($ruleId, EarningRule::TYPE_POINTS, [
            'name' => 'test',
            'description' => 'desc',
            'startAt' => (new \DateTime())->getTimestamp(),
            'endAt' => (new \DateTime('+1 month'))->getTimestamp(),
            'pointValue' => 3.3,
        ]);
        $handler->handle($command);
        $rule = $this->inMemoryRepository->byId($ruleId);
        $this->assertNotNull($rule);
        $this->assertInstanceOf(PointsEarningRule::class, $rule);
    }

    /**
     * @test
     */
    public function it_creates_new_points_earning_rule_with_included_labels()
    {
        $handler = $this->createCommandHandler();
        $ruleId = new EarningRuleId('00000000-0000-0000-0000-000000000000');

        $command = new CreateEarningRule($ruleId, EarningRule::TYPE_POINTS, [
            'name' => 'test',
            'description' => 'desc',
            'startAt' => (new \DateTime())->getTimestamp(),
            'endAt' => (new \DateTime('+1 month'))->getTimestamp(),
            'pointValue' => 3.3,
            'labelsInclusionType' => PointsEarningRule::LABELS_INCLUSION_TYPE_INCLUDE,
            'includedLabels' => [
                ['key' => 'manufacturer', 'value' => 'nike'],
                ['key' => 'manufacturer', 'value' => 'adidas'],
            ],
        ]);
        $handler->handle($command);
        $rule = $this->inMemoryRepository->byId($ruleId);
        $this->assertNotNull($rule);
        $this->assertInstanceOf(PointsEarningRule::class, $rule);
        $this->assertEquals(PointsEarningRule::LABELS_INCLUSION_TYPE_INCLUDE, $rule->getLabelsInclusionType());
        $this->assertCount(2, $rule->getIncludedLabels());
    }

    /**
     * @test
     */
    public function it_creates_new_product_purchase_earning_rule()
    {
        $handler = $this->createCommandHandler();
        $ruleId = new EarningRuleId('00000000-0000-0000-0000-000000000000');

        $command = new CreateEarningRule($ruleId, EarningRule::TYPE_PRODUCT_PURCHASE, [
            'name' => 'test',
            'description' => 'desc',
            'startAt' => (new \DateTime())->getTimestamp(),
            'endAt' => (new \DateTime('+1 month'))->getTimestamp(),
            'skuIds' => ['123'],
            'pointsAmount' => 100,
        ]);
        $handler->handle($command);
        $rule = $this->inMemoryRepository->byId($ruleId);
        $this->assertNotNull($rule);
        $this->assertInstanceOf(ProductPurchaseEarningRule::class, $rule);
    }

    /**
     * @test
     */
    public function it_creates_new_instant_reward_earning_rule()
    {
        $handler = $this->createCommandHandler();
        $ruleId = new EarningRuleId('00000000-0000-0000-0000-000000000000');

        $command = new CreateEarningRule($ruleId, EarningRule::TYPE_INSTANT_REWARD, [
            'name' => 'test',
            'description' => 'desc',
            'startAt' => (new \DateTime())->getTimestamp(),
            'endAt' => (new \DateTime('+1 month'))->getTimestamp(),
            'skuIds' => ['123'],
            'pointsAmount' => 100,
            'rewardCampaignId' => '00000000-0000-0000-0000-000000000000',
        ]);
        $handler->handle($command);
        $rule = $this->inMemoryRepository->byId($ruleId);
        $this->assertNotNull($rule);
        $this->assertInstanceOf(InstantRewardRule::class, $rule);
    }
}
