<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use OpenLoyalty\Component\Campaign\Domain\Command\CampaignCommandHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class CampaignCommandHandlerTest.
 */
abstract class CampaignCommandHandlerTest extends TestCase
{
    /**
     * @var CampaignRepository
     */
    protected $inMemoryRepository;

    protected $campaigns = [];

    public function setUp()
    {
        $campaigns = &$this->campaigns;
        $this->inMemoryRepository = $this->getMockBuilder(CampaignRepository::class)->getMock();
        $this->inMemoryRepository->method('save')->with($this->isInstanceOf(Campaign::class))->will(
            $this->returnCallback(function ($campaign) use (&$campaigns) {
                $campaigns[] = $campaign;

                return $campaign;
            })
        );
        $this->inMemoryRepository->method('findAll')->with()->will(
            $this->returnCallback(function () use (&$campaigns) {
                return $campaigns;
            })
        );
        $this->inMemoryRepository->method('byId')->with($this->isInstanceOf(CampaignId::class))->will(
            $this->returnCallback(function ($id) use (&$campaigns) {
                /** @var Campaign $campaign */
                foreach ($campaigns as $campaign) {
                    if ($campaign->getCampaignId()->__toString() == $id->__toString()) {
                        return $campaign;
                    }
                }

                return;
            })
        );
    }

    protected function createCommandHandler()
    {
        return new CampaignCommandHandler(
            $this->inMemoryRepository
        );
    }
}
