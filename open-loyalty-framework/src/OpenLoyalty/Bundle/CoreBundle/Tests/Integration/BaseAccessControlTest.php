<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CoreBundle\Tests\Integration;

use Symfony\Component\HttpKernel\Client;

/**
 * Class BaseAccessControlTest.
 */
abstract class BaseAccessControlTest extends BaseApiTest
{
    /**
     * @param array  $clients
     * @param string $route
     * @param array  $params
     * @param string $method
     */
    protected function checkClients(array $clients, string $route, array $params = [], string $method = 'GET')
    {
        foreach ($clients as $clientData) {
            /** @var Client $client */
            $client = $clientData['client'];
            $client->insulate(true);
            $client->request(
                $method,
                $route,
                $params
            );
            $response = $client->getResponse();
            $statusCode = $response->getStatusCode();
            if (isset($clientData['status'])) {
                $this->assertSame(
                    $clientData['status'],
                    $statusCode,
                    $clientData['status'].' should be returned instead '.$statusCode.', client: '.(isset($clientData['name']) ? $clientData['name'] : null)
                );
            }

            if (isset($clientData['not_status'])) {
                $this->assertNotSame(
                    $clientData['not_status'],
                    $statusCode,
                    $clientData['not_status'].' should not be returned, instead '.$statusCode.' returned, client: '.(isset($clientData['name']) ? $clientData['name'] : null)
                );
            }
        }
    }

    /**
     * @return Client
     */
    protected function getCustomerClient(): Client
    {
        return $this->createAuthenticatedClient('user@oloy.com', 'loyalty', 'customer');
    }

    /**
     * @return Client
     */
    protected function getAdminClient(): Client
    {
        return $this->createAuthenticatedClient();
    }

    /**
     * @return Client
     */
    protected function getSellerClient(): Client
    {
        return $this->createAuthenticatedClient('john@doe.com', 'open', 'seller');
    }

    /**
     * @return Client
     */
    protected function getSellerWithDisallowedSpendPointClient(): Client
    {
        return $this->createAuthenticatedClient('john2@doe2.com', 'open', 'seller');
    }
}
