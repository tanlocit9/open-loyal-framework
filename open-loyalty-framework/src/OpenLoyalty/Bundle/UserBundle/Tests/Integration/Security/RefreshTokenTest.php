<?php

namespace OpenLoyalty\Bundle\UserBundle\Tests\Integration\Security;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadAdminData;

/**
 * Class RefreshTokenTest.
 */
class RefreshTokenTest extends BaseApiTest
{
    /**
     * @test
     */
    public function it_allows_to_obtain_new_token_based_on_refresh_token()
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/admin/login_check',
            array(
                '_username' => LoadAdminData::ADMIN_USERNAME,
                '_password' => LoadAdminData::ADMIN_PASSWORD,
            )
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $token = $data['token'];
        $refreshToken = $data['refresh_token'];

        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));

        $client->request(
            'POST',
            '/api/token/refresh',
            [
                'refresh_token' => $refreshToken,
            ]
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(isset($data['refresh_token']), 'Response should have field "refresh_token". '.$client->getResponse()->getContent());
    }
}
