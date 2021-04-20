<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;

/**
 * Class InvitationControllerTest.
 */
final class InvitationControllerTest extends BaseApiTest
{
    /**
     * @test
     */
    public function it_allows_to_send_email_invitation_without_type(): void
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'POST',
            '/api/invitations/invite',
            [
                'invitation' => [
                    'recipientEmail' => 'test_referral_wt@example.com',
                ],
            ]
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
    }

    /**
     * @test
     */
    public function it_allows_to_send_email_invitation(): void
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'POST',
            '/api/invitations/invite',
            [
                'invitation' => [
                    'type' => 'email',
                    'recipientEmail' => 'test_referral@example.com',
                ],
            ]
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
    }

    /**
     * @test
     * @depends it_allows_to_send_email_invitation
     */
    public function it_allows_to_send_duplicated_email_invitation(): void
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'POST',
            '/api/invitations/invite',
            [
                'invitation' => [
                    'type' => 'email',
                    'recipientEmail' => 'test_referral@example.com',
                ],
            ]
        );
        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode(), 'Response should have status 200');
    }

    /**
     * @test
     */
    public function it_allows_to_send_mobile_invitation(): void
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'POST',
            '/api/invitations/invite',
            [
                'invitation' => [
                    'type' => 'mobile',
                    'recipientPhone' => '123456789',
                ],
            ]
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
    }

    /**
     * @test
     * @depends it_allows_to_send_mobile_invitation
     */
    public function it_allows_to_send_duplicated_mobile_invitation(): void
    {
        $client = $this->createAuthenticatedClient(LoadUserData::USER_USERNAME, LoadUserData::USER_PASSWORD, 'customer');
        $client->request(
            'POST',
            '/api/invitations/invite',
            [
                'invitation' => [
                    'type' => 'mobile',
                    'recipientPhone' => '123456789',
                ],
            ]
        );
        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode(), 'Response should have status 200');
    }
}
