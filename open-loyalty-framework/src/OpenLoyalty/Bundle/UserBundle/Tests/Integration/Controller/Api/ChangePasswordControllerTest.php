<?php

namespace OpenLoyalty\Bundle\UserBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;

/**
 * Class ChangePasswordControllerTest.
 */
class ChangePasswordControllerTest extends BaseApiTest
{
    /**
     * @test
     */
    public function it_allows_to_change_current_password()
    {
        $client = $this->createAuthenticatedClient('user-temp@oloy.com', 'loyalty', 'customer');
        $client->request(
            'POST',
            '/api/customer/password/change',
            [
                'currentPassword' => 'loyalty',
                'plainPassword' => 'NoweHaslo123!',
            ]
        );

        $response = $client->getResponse()->getContent();

        $data = json_decode($response, true);
        $this->assertTrue($data['success']);
        $this->createAuthenticatedClient('user-temp@oloy.com', 'NoweHaslo123!', 'customer');
    }
}
