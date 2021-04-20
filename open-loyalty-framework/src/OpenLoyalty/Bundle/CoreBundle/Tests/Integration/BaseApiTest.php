<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Bundle\CoreBundle\Tests\Integration;

use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadAdminData;
use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Service\MasterAdminProvider;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BaseApiTest.
 */
abstract class BaseApiTest extends WebTestCase
{
    const MASTER_KEY_TOKEN = '1234';

    /**
     * @return Client
     */
    protected function createAuthenticatedClientUsingMasterKey(): Client
    {
        $client = static::createClient();
        $container = static::$kernel->getContainer();
        $container->set(
            'OpenLoyalty\Bundle\UserBundle\Service\MasterAdminProvider',
            new MasterAdminProvider(
                self::MASTER_KEY_TOKEN,
                $container->get('oloy.user.user_manager')
            )
        );

        return $client;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $type
     *
     * @return Client
     */
    protected static function createAuthenticatedClient(
        $username = LoadAdminData::ADMIN_USERNAME,
        $password = LoadAdminData::ADMIN_PASSWORD,
        string $type = 'admin'
    ): Client {
        $client = static::createClient();
        $client->request(
            'POST',
            sprintf('/api/%s/login_check', $type),
            [
                '_username' => $username,
                '_password' => $password,
            ]
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        static::assertTrue(
            isset($data['token']),
            sprintf(
                'Response should have field "token". %s%s',
                $client->getResponse()->getContent(),
                json_encode(
                    [
                        '/api/'.$type.'/login_check',
                        [
                            '_username' => $username,
                            '_password' => $password,
                        ],
                    ]
                )
            )
        );
        static::assertTrue(
            isset($data['refresh_token']),
            sprintf(
                'Response should have field "refresh_token". %s',
                $client->getResponse()->getContent()
            )
        );

        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }

    /**
     * @param Client $client
     * @param string $customerId
     *
     * @return float
     */
    protected function getCustomerPoints(Client $client, string $customerId): float
    {
        $client->request('GET', sprintf('/api/customer/%s/status', $customerId));

        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('points', $data);

        return (float) $data['points'];
    }

    /**
     * @param $customerEmail
     *
     * @return string
     */
    protected function getActivateTokenForCustomer($customerEmail): string
    {
        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');

        $activateToken = $entityManager
            ->getRepository('OpenLoyaltyUserBundle:Customer')
            ->findOneBy(['email' => $customerEmail])
            ->getActionToken()
        ;

        return $activateToken;
    }

    /**
     * @param string $customerId
     *
     * @return null|Customer
     */
    protected function getCustomerEntity($customerId): ?Customer
    {
        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $customer = $entityManager->getRepository('OpenLoyaltyUserBundle:Customer')->find($customerId);

        return $customer;
    }

    /**
     * @param string $adminId
     *
     * @return null|Admin
     */
    protected function getAdminEntity(string $adminId): ?Admin
    {
        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $admin = $entityManager->getRepository('OpenLoyaltyUserBundle:Admin')->find($adminId);

        return $admin;
    }

    /**
     * @param Response $response
     */
    protected function assertOkResponseStatus(Response $response): void
    {
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
    }
}
