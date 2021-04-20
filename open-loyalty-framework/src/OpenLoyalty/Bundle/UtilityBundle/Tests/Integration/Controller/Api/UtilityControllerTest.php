<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UtilityBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\LevelBundle\DataFixtures\ORM\LoadLevelData;
use OpenLoyalty\Bundle\SegmentBundle\DataFixtures\ORM\LoadSegmentData;

/**
 * Class UtilityControllerTest.
 */
class UtilityControllerTest extends BaseApiTest
{
    /**
     * @test
     */
    public function it_returns_segments_csv()
    {
        ob_start();
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/csv/segment/'.LoadSegmentData::SEGMENT_ID
        );
        $response = $client->getResponse();
        $statusCode = $response->getStatusCode();
        $contentType = $response->headers->get('Content-Type');
        ob_get_clean();
        $this->assertEquals(200, $statusCode);
        $this->assertEquals('text/csv; charset=utf-8', $contentType);
    }

    /**
     * @test
     */
    public function it_returns_levels_csv()
    {
        ob_start();
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/csv/level/'.LoadLevelData::LEVEL1_ID
        );
        $response = $client->getResponse();
        $statusCode = $response->getStatusCode();
        $contentType = $response->headers->get('Content-Type');
        ob_get_clean();
        $this->assertEquals(200, $statusCode);
        $this->assertEquals('text/csv; charset=utf-8', $contentType);
    }
}
