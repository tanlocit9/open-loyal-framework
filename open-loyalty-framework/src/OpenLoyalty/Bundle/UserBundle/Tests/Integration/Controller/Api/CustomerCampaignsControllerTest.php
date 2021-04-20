<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;

/**
 * Class CustomerCampaignsControllerTest.
 */
class CustomerCampaignsControllerTest extends BaseApiTest
{
    /**
     * @test
     * @dataProvider getCampaignsFilters
     *
     * @param array $filters
     * @param int   $expectedCount
     */
    public function it_gets_customer_available_campaigns(array $filters, int $expectedCount): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/admin/customer/'.LoadUserData::USER2_USER_ID.'/campaign/available',
            $filters
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('campaigns', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertCount($expectedCount, $data['campaigns']);
        $this->assertEquals($expectedCount, $data['total']);
    }

    /**
     * @return array
     */
    public function getCampaignsFilters(): array
    {
        return [
            [['isFeatured' => 1], 0],
            [['isFeatured' => 0], 2],
            [['isPublic' => 1], 2],
            [['isPublic' => 0], 0],
        ];
    }
}
