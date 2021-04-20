<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\CampaignBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;

/**
 * Class CampaignPhotoControllerTest.
 */
final class CampaignPhotoControllerTest extends BaseApiTest
{
    private const CAMPAIGN_ID = '000096cf-32a3-43bd-9034-4df343e5fd93';

    /**
     * @test
     */
    public function it_return_404_response_when_try_to_get_non_existing_photo(): void
    {
        $client = $this->createAuthenticatedClient();

        $nonExistsPhotoId = '00000000-0000-43bd-9034-4df343e5fd92';
        $uri = sprintf('/api/campaign/%s/photo/%s', self::CAMPAIGN_ID, $nonExistsPhotoId);
        $client->request('GET', $uri);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_return_404_response_when_try_to_remove_not_existing_photo(): void
    {
        $client = $this->createAuthenticatedClient();

        $nonExistsPhotoId = '00000000-0000-43bd-9034-4df343e5fd92';
        $uri = sprintf('/api/campaign/%s/photo/%s', self::CAMPAIGN_ID, $nonExistsPhotoId);
        $client->request('DELETE', $uri);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_return_404_response_when_try_to_remove_photo_for_not_exists_campaign(): void
    {
        $client = $this->createAuthenticatedClient();

        $nonExistsPhotoId = '00000000-0000-43bd-9034-4df343e5fd92';
        $nonExistsCampaign = '00000000-0000-43bd-9034-4df343e50000';
        $uri = sprintf('/api/campaign/%s/photo/%s', $nonExistsCampaign, $nonExistsPhotoId);
        $client->request('DELETE', $uri);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_return_404_response_when_parameter_on_uri_is_not_set(): void
    {
        $client = $this->createAuthenticatedClient();

        $uri = sprintf('/api/campaign/%s/photo/%s', '', '');
        $client->request('GET', $uri);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_return_404_response_when_try_to_add_photo_to_not_exists_campaign(): void
    {
        $client = $this->createAuthenticatedClient();

        $nonExistsCampaign = '00000000-0000-43bd-9034-4df343e50000';
        $client->request('POST', sprintf('/api/campaign/%s/photo', $nonExistsCampaign));

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_return_404_response_when_try_to_add_photo_with_incorrect_campaign_id(): void
    {
        $client = $this->createAuthenticatedClient();

        $invalidCampaignId = 'campaign';
        $uri = sprintf('/api/campaign/%s/photo', $invalidCampaignId);
        $client->request('POST', $uri);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
