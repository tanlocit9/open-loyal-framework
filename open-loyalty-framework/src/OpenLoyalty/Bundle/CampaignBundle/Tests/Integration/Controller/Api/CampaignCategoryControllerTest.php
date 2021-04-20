<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CampaignBundle\DataFixtures\ORM\LoadCampaignData;
use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryId;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryRepository;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategory;

/**
 * Class CampaignCategoryControllerTest.
 */
class CampaignCategoryControllerTest extends BaseApiTest
{
    /**
     * @var CampaignCategoryRepository
     */
    protected $campaignCategoryRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        static::bootKernel();
        $this->campaignCategoryRepository = static::$kernel->getContainer()->get('OpenLoyalty\Component\Campaign\Infrastructure\Persistence\Doctrine\Repository\DoctrineCampaignCategoryRepository');
    }

    /**
     * @test
     */
    public function it_creates_campaign_category(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/campaignCategory',
            [
                'campaign_category' => [
                    'active' => 1,
                    'sortOrder' => 0,
                    'translations' => [
                        'en' => [
                            'name' => 'Campaign category A EN',
                        ],
                        'pl' => [
                            'name' => 'Campaign category A PL',
                        ],
                    ],
                ],
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('campaignCategoryId', $data);

        $campaignCategory = $this->campaignCategoryRepository->byId(new CampaignCategoryId($data['campaignCategoryId']));
        $this->assertInstanceOf(CampaignCategory::class, $campaignCategory);
        $this->assertEquals('Campaign category A EN', $campaignCategory->getName());
        $this->assertEquals(true, $campaignCategory->isActive());
        $this->assertEquals(0, $campaignCategory->getSortOrder());
    }

    /**
     * @test
     */
    public function it_modifies_campaign_category(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'PUT',
            '/api/campaignCategory/'.LoadCampaignData::CAMPAIGN_CATEGORY1_ID,
            [
                'campaign_category' => [
                    'active' => 1,
                    'sortOrder' => 0,
                    'translations' => [
                        'en' => [
                            'name' => 'Campaign category A EN modified',
                        ],
                        'pl' => [
                            'name' => 'Campaign category A PL modified',
                        ],
                    ],
                ],
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('campaignCategoryId', $data);

        $campaignCategory = $this->campaignCategoryRepository->byId(new CampaignCategoryId($data['campaignCategoryId']));
        $this->assertInstanceOf(CampaignCategory::class, $campaignCategory);
        $this->assertEquals('Campaign category A EN modified', $campaignCategory->getName());
        $this->assertEquals(true, $campaignCategory->isActive());
        $this->assertEquals(0, $campaignCategory->getSortOrder());
    }

    /**
     * @test
     */
    public function it_actives_campaign_category(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/campaignCategory/'.LoadCampaignData::CAMPAIGN_CATEGORY1_ID.'/active',
            [
                'active' => false,
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode(), 'Response should have status 200');
        $campaignCategory = $this->campaignCategoryRepository->byId(new CampaignCategoryId(LoadCampaignData::CAMPAIGN_CATEGORY1_ID));
        $this->assertInstanceOf(CampaignCategory::class, $campaignCategory);
        $this->assertEquals(false, $campaignCategory->isActive());
    }
}
