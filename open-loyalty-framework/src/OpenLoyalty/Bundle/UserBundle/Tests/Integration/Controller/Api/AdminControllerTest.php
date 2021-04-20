<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadAdminData;
use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminControllerTest.
 */
class AdminControllerTest extends BaseApiTest
{
    /**
     * @test
     */
    public function it_allows_to_get_administrators_list()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/admin'
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $this->assertArrayHasKey('users', $data);
        $this->assertNotEmpty($data['users']);
        foreach ($data['users'] as $user) {
            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey('username', $user);
            $this->assertArrayHasKey('isActive', $user);
            $this->assertArrayHasKey('createAt', $user);
            $this->assertArrayHasKey('email', $user);
            $this->assertArrayHasKey('external', $user);
            $this->assertArrayHasKey('dtype', $user);
            $this->assertEquals($user['dtype'], 'admin');
        }
    }

    /**
     * @test
     *
     * @depends it_allows_to_get_administrators_list
     */
    public function it_allows_to_update_data_currently_logged_in_administrator()
    {
        $client = $this->createAuthenticatedClient();

        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $role = $entityManager->getRepository('OpenLoyaltyUserBundle:Role')->findOneBy(['name' => 'Reporter admin']);

        $client->request(
            'PUT',
            '/api/admin/data/'.LoadAdminData::ADMIN_ID,
            [
                'admin' => [
                    'email' => 'test2@example.com',
                    'firstName' => 'John',
                    'lastName' => 'Smith',
                    'phone' => '+48123123123',
                    'roles' => [$role->getId()],
                ],
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $admin = $this->getAdminEntity(LoadAdminData::ADMIN_ID);
        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertEquals('test2@example.com', $admin->getEmail());
        $this->assertEquals('John', $admin->getFirstName());
        $this->assertEquals('Smith', $admin->getLastName());
        $this->assertEquals('+48123123123', $admin->getPhone());
    }

    /**
     * @test
     *
     * @depends it_allows_to_get_administrators_list
     */
    public function it_not_allows_to_disable_currently_logged_in_administrator()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'PUT',
            '/api/admin/data/'.LoadAdminData::ADMIN_ID,
            [
                'admin' => [
                    'isActive' => false,
                ],
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode(), 'Response should have status 400');
    }

    /**
     * @test
     */
    public function it_allows_to_create_a_new_administrator_account()
    {
        $client = $this->createAuthenticatedClient();

        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        /** @var Role $role */
        $role = $entityManager->getRepository('OpenLoyaltyUserBundle:Role')->findOneBy(['name' => 'Reporter admin']);

        $client->request(
            'POST',
            '/api/admin/data',
            [
                'admin' => [
                    'email' => 'test3@example.com',
                    'firstName' => 'Alice',
                    'lastName' => 'May',
                    'phone' => '+48123123123',
                    'plainPassword' => 'Test12#$',
                    'isActive' => true,
                    'roles' => [
                        $role->getId(),
                    ],
                ],
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $responseBody = json_decode($response->getContent());
        $adminId = $responseBody->adminId;

        $admin = $this->getAdminEntity($adminId);
        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertEquals('test3@example.com', $admin->getEmail());
        $this->assertEquals('Alice', $admin->getFirstName());
        $this->assertEquals('May', $admin->getLastName());
        $this->assertEquals('+48123123123', $admin->getPhone());
        $this->assertEquals(1, count($admin->getRoles()));
        $this->assertTrue($admin->getIsActive());
    }

    /**
     * @test
     */
    public function it_allows_to_create_a_new_external_administrator_account()
    {
        $client = $this->createAuthenticatedClient();

        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        /** @var Role $role */
        $role = $entityManager->getRepository('OpenLoyaltyUserBundle:Role')->findOneBy(['name' => 'Reporter admin']);

        $client->request(
            'POST',
            '/api/admin/data',
            [
                'admin' => [
                    'email' => 'test4@example.com',
                    'firstName' => 'Joanna',
                    'lastName' => 'Marks',
                    'phone' => '+48123123123',
                    'external' => true,
                    'isActive' => true,
                    'apiKey' => '123456',
                    'roles' => [$role->getId()],
                ],
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $responseBody = json_decode($response->getContent());
        $adminId = $responseBody->adminId;

        $admin = $this->getAdminEntity($adminId);
        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertEquals('test4@example.com', $admin->getEmail());
        $this->assertEquals('Joanna', $admin->getFirstName());
        $this->assertEquals('Marks', $admin->getLastName());
        $this->assertEquals('+48123123123', $admin->getPhone());
        $this->assertTrue($admin->isExternal());
        $this->assertTrue($admin->getIsActive());
        $this->assertEquals('123456', $admin->getApiKey());
    }

    /**
     * @test
     *
     * @depends it_allows_to_create_a_new_external_administrator_account
     */
    public function it_allows_to_disable_an_administrator_account()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/admin'
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $this->assertArrayHasKey('users', $data);
        $this->assertNotEmpty($data['users']);

        $admins = array_filter($data['users'], function ($admin) {
            return $admin['id'] !== LoadAdminData::ADMIN_ID;
        });

        $admin = reset($admins);
        $role = reset($admin['roles']);

        $client->request(
            'PUT',
            '/api/admin/data/'.$admin['id'],
            [
                'admin' => [
                    'email' => $admin['email'],
                    'isActive' => false,
                    'roles' => [$role['id']],
                ],
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Response should have status 200 ');

        $admin = $this->getAdminEntity($admin['id']);
        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertFalse($admin->getIsActive());
    }
}
