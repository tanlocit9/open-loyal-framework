<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\EarningRuleBundle\Security\Voter\EarningRuleVoter;
use OpenLoyalty\Bundle\LevelBundle\Security\Voter\LevelVoter;
use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;

/**
 * Class AclControllerTest.
 */
final class AclControllerTest extends BaseApiTest
{
    /**
     * @param string $name
     *
     * @return null|Role $name
     */
    protected function getRoleEntityBy(string $name): ?Role
    {
        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        return $entityManager->getRepository('OpenLoyaltyUserBundle:Role')->findOneBy(['name' => $name]);
    }

    /**
     * @test
     */
    public function it_allows_to_get_accesses_list(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/admin/acl/accesses'
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $this->assertArrayHasKey('accesses', $data);
        $this->assertNotEmpty($data['accesses']);
    }

    /**
     * @test
     */
    public function it_allows_to_get_resource_list(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/admin/acl/resources'
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $this->assertArrayHasKey('resources', $data);
        $this->assertNotEmpty($data['resources']);
    }

    /**
     * @test
     */
    public function it_allows_to_create_a_new_role(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/admin/acl/role',
            [
                'role' => [
                    'name' => 'Super test role',
                    'permissions' => [
                        [
                            'resource' => LevelVoter::PERMISSION_RESOURCE,
                            'access' => PermissionAccess::VIEW,
                        ],
                        [
                            'resource' => LevelVoter::PERMISSION_RESOURCE,
                            'access' => PermissionAccess::MODIFY,
                        ],
                    ],
                ],
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode(), 'Response should have status 204');

        $role = $this->getRoleEntityBy('Super test role');
        $this->assertEquals('Super test role', $role->getName());
        $this->assertTrue($role->hasPermission(LevelVoter::PERMISSION_RESOURCE, PermissionAccess::VIEW));
        $this->assertTrue($role->hasPermission(LevelVoter::PERMISSION_RESOURCE, PermissionAccess::MODIFY));
    }

    /**
     * @test
     *
     * @depends it_allows_to_create_a_new_role
     */
    public function it_allows_to_update_role(): void
    {
        $client = $this->createAuthenticatedClient();

        $role = $this->getRoleEntityBy('Super test role');

        $client->request(
            'PUT',
            '/api/admin/acl/role/'.$role->getId(),
            [
                'role' => [
                    'name' => 'Super test role updated',
                    'permissions' => [
                        [
                            'resource' => EarningRuleVoter::PERMISSION_RESOURCE,
                            'access' => PermissionAccess::VIEW,
                        ],
                        [
                            'resource' => EarningRuleVoter::PERMISSION_RESOURCE,
                            'access' => PermissionAccess::MODIFY,
                        ],
                    ],
                ],
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode(), 'Response should have status 204');

        $role = $this->getRoleEntityBy('Super test role updated');
        $this->assertEquals('Super test role updated', $role->getName());
        $this->assertFalse($role->hasPermission(LevelVoter::PERMISSION_RESOURCE, PermissionAccess::VIEW));
        $this->assertFalse($role->hasPermission(LevelVoter::PERMISSION_RESOURCE, PermissionAccess::MODIFY));
        $this->assertTrue($role->hasPermission(EarningRuleVoter::PERMISSION_RESOURCE, PermissionAccess::VIEW));
        $this->assertTrue($role->hasPermission(EarningRuleVoter::PERMISSION_RESOURCE, PermissionAccess::MODIFY));
    }

    /**
     * @test
     *
     * @depends it_allows_to_update_role
     */
    public function it_allows_to_get_role(): void
    {
        $client = $this->createAuthenticatedClient();

        $role = $this->getRoleEntityBy('Super test role updated');

        $client->request(
            'GET',
            '/api/admin/acl/role/'.$role->getId()
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('Super test role updated', $data['name']);

        $this->assertArrayHasKey('role', $data);
        $this->assertEquals('ROLE_ADMIN', $data['role']);

        $this->assertArrayHasKey('master', $data);
        $this->assertEquals(false, $data['master']);

        $this->assertArrayHasKey('permissions', $data);
        $this->assertEquals(2, count($data['permissions']));

        $this->assertArrayHasKey('resource', $data['permissions'][0]);
        $this->assertArrayHasKey('access', $data['permissions'][0]);
    }

    /**
     * @test
     *
     * @depends it_allows_to_create_a_new_role
     */
    public function it_allows_to_delete_role(): void
    {
        $client = $this->createAuthenticatedClient();

        $role = $this->getRoleEntityBy('Super test role updated');

        $client->request(
            'DELETE',
            '/api/admin/acl/role/'.$role->getId()
        );

        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode(), 'Response should have status 204');
    }

    /**
     * @test
     */
    public function it_does_not_allow_to_delete_master_role(): void
    {
        $client = $this->createAuthenticatedClient();

        $role = $this->getRoleEntityBy('Super admin');

        $client->request(
            'DELETE',
            '/api/admin/acl/role/'.$role->getId()
        );

        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode(), 'Response should have status 400');
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Can not remove master role.', $data['error']);
    }

    /**
     * @test
     */
    public function it_allows_to_get_role_list(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'GET',
            '/api/admin/acl/role'
        );
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $this->assertArrayHasKey('roles', $data);
        $this->assertNotEmpty($data['roles']);
    }
}
