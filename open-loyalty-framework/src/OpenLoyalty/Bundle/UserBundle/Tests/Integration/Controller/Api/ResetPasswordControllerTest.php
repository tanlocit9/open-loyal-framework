<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;

/**
 * Class ResetPasswordControllerTest.
 */
class ResetPasswordControllerTest extends BaseApiTest
{
    /**
     * @test
     *
     * @dataProvider getUsername
     *
     * @param string $username
     */
    public function it_sends_reset_password_link($username)
    {
        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/customer/password/reset/request',
            [
                'username' => $username,
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200 '.$response->getContent());
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    /**
     * @test
     *
     * @dataProvider getUsername
     *
     * @param string $username
     */
    public function it_does_not_allows_to_reset_password_second_time($username)
    {
        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/customer/password/reset/request',
            [
                'username' => $username,
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(400, $response->getStatusCode(), 'Response should have status 400 '.$response->getContent());
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('resetting password already requested', $data['error']);
    }

    /**
     * @test
     */
    public function it_returns_not_found_if_user_does_not_exists()
    {
        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/customer/password/reset/request',
            [
                'username' => 'non_exist_user_name',
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(404, $response->getStatusCode(), 'Response should have status 404 '.$response->getContent());
        $this->assertArrayHasKey('error', $data);
        $errorData = $data['error'];
        $this->assertArrayHasKey('code', $errorData);
        $this->assertArrayHasKey('message', $errorData);
        $this->assertEquals('404', $errorData['code']);
        $this->assertEquals('Not Found', $errorData['message']);
    }

    /**
     * @return array
     */
    public function getUsername()
    {
        return [
            [LoadUserData::USER_USERNAME],
            [LoadUserData::USER1_PHONE_NUMBER],
        ];
    }
}
