<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Tests\Integration\Infrastructure\Persistance\Repository;

use Doctrine\ORM\EntityManagerInterface;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignRepositoryInterface;
use OpenLoyalty\Component\Campaign\Infrastructure\Doctrine\ORM\Repository\CampaignRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CampaignRepositoryTest.
 */
class CampaignRepositoryTest extends KernelTestCase
{
    private const CAMPAIGN_ID = '000096cf-32a3-43bd-9034-4df343e5fd93';

    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $this->entityManager = self::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        $this->campaignRepository = new CampaignRepository($this->entityManager);
    }

    /**
     * @test
     */
    public function it_load_single_campaign_by_id(): void
    {
        $campaign = $this->campaignRepository->findOneById(new CampaignId(self::CAMPAIGN_ID));
        $this->assertNotNull($campaign);
    }

    /**
     * @test
     */
    public function it_return_null_when_campaign_not_found_by_id(): void
    {
        $notExistsCampaignId = new CampaignId('00000000-0000-0000-0000-000043e5fd93');
        $result = $this->campaignRepository->findOneById($notExistsCampaignId);
        $this->assertNull($result);
    }
}
