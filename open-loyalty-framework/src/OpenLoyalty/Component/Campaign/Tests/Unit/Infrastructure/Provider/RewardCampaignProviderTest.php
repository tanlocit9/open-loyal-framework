<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Tests\Unit\Infrastructure\Provider;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Infrastructure\Provider\RewardCampaignProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class RewardCampaignProviderTest.
 */
final class RewardCampaignProviderTest extends TestCase
{
    /**
     * @var CampaignRepository|MockObject
     */
    private $repository;

    /**
     * @var Campaign|MockObject
     */
    private $campaign;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(CampaignRepository::class);

        $this->campaign = $this->createMock(Campaign::class);
        $this->campaign->method('getName')->willReturn('Campaign name');
        $this->campaign->method('getConditionsDescription')->willReturn('Campaign description');
    }

    /**
     * @test
     */
    public function it_returns_campaign(): void
    {
        $this->repository->expects($this->once())->method('byId')->willReturn($this->campaign);

        $provider = new RewardCampaignProvider($this->repository);
        $campaign = $provider->findById(new CampaignId('00000000-0000-0000-0000-000000000000'));

        $this->assertSame('Campaign name', $campaign->getName());
        $this->assertSame('Campaign description', $campaign->getConditionsDescription());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_campaign_not_found(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->repository->expects($this->once())->method('byId')->willReturn(null);

        $provider = new RewardCampaignProvider($this->repository);
        $provider->findById(new CampaignId('00000000-0000-0000-0000-000000000000'));
    }
}
