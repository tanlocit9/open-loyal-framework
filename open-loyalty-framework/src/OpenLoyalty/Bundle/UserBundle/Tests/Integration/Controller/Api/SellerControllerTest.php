<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Integration\Controller\Api;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\PosBundle\DataFixtures\ORM\LoadPosData;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetails;

/**
 * Class SellerControllerTest.
 */
class SellerControllerTest extends BaseApiTest
{
    /**
     * @test
     */
    public function it_allows_to_register_new_seller()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/seller/register',
            [
                'seller' => [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => 'jane@doe.com',
                    'phone' => '+48123123123',
                    'posId' => LoadPosData::POS_ID,
                    'plainPassword' => 'oloy',
                    'active' => 1,
                    'allowPointTransfer' => true,
                ],
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('sellerId', $data);
        $this->assertArrayHasKey('password', $data);
        $this->assertArrayHasKey('email', $data);
        $this->createAuthenticatedClient($data['email'], $data['password'], 'seller');
    }

    /**
     * @test
     */
    public function it_not_allows_to_register_new_seller_with_the_same_email()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/seller/register',
            [
                'seller' => [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => 'jane@doe.com',
                    'phone' => '+48123123123',
                    'posId' => LoadPosData::POS_ID,
                    'plainPassword' => 'oloy',
                ],
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode(), 'Response should have status 400');
    }

    /**
     * @test
     */
    public function it_allows_to_edit_seller_details()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'PUT',
            '/api/seller/'.LoadUserData::TEST_SELLER_ID,
            [
                'seller' => [
                    'firstName' => 'Jane',
                    'lastName' => 'Doe',
                    'email' => 'john@doe.com',
                    'phone' => '+48123123123',
                    'posId' => LoadPosData::POS_ID,
                    'allowPointTransfer' => true,
                ],
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        self::$kernel->boot();
        /** @var Repository $repo */
        $repo = self::$kernel->getContainer()->get('oloy.user.read_model.repository.seller_details');
        /** @var SellerDetails $seller */
        $seller = $repo->find(LoadUserData::TEST_SELLER_ID);

        $this->assertEquals('Jane', $seller->getFirstName());
        $this->assertTrue($seller->isAllowPointTransfer());
    }

    /**
     * @test
     */
    public function it_allows_to_deactivate_seller()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/seller/'.LoadUserData::TEST_SELLER_ID.'/deactivate'
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        self::$kernel->boot();
        /** @var Repository $repo */
        $repo = self::$kernel->getContainer()->get('oloy.user.read_model.repository.seller_details');
        /** @var SellerDetails $seller */
        $seller = $repo->find(LoadUserData::TEST_SELLER_ID);

        $this->assertTrue(!$seller->isActive());
    }

    /**
     * @test
     */
    public function it_allows_to_activate_seller()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/seller/'.LoadUserData::TEST_SELLER_ID.'/activate'
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        self::$kernel->boot();
        /** @var Repository $repo */
        $repo = self::$kernel->getContainer()->get('oloy.user.read_model.repository.seller_details');
        /** @var SellerDetails $seller */
        $seller = $repo->find(LoadUserData::TEST_SELLER_ID);

        $this->assertTrue($seller->isActive());
    }
}
