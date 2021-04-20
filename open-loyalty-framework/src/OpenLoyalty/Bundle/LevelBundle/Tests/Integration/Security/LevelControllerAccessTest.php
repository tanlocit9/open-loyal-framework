<?php

namespace OpenLoyalty\Bundle\LevelBundle\Tests\Integration\Security;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseAccessControlTest;
use OpenLoyalty\Bundle\LevelBundle\DataFixtures\ORM\LoadLevelData;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LevelControllerAccessTest.
 */
class LevelControllerAccessTest extends BaseAccessControlTest
{
    /**
     * @test
     */
    public function only_admin_and_seller_should_have_access_to_all_level_list()
    {
        $clients = [
            ['client' => $this->getCustomerClient(), 'status' => 403, 'name' => 'customer'],
            ['client' => $this->getSellerClient(), 'not_status' => 403, 'name' => 'seller'],
            ['client' => $this->getAdminClient(), 'not_status' => 403, 'name' => 'admin'],
        ];

        $this->checkClients($clients, '/api/level');
    }

    /**
     * @test
     */
    public function only_admin_can_create_level()
    {
        $clients = [
            ['client' => $this->getCustomerClient(), 'status' => 403, 'name' => 'customer'],
            ['client' => $this->getSellerClient(), 'status' => 403, 'name' => 'seller'],
            ['client' => $this->getAdminClient(), 'not_status' => 403, 'name' => 'admin'],
        ];

        $this->checkClients($clients, '/api/level/create', [], 'POST');
    }

    /**
     * @test
     */
    public function only_admin_can_activate_level()
    {
        $clients = [
            ['client' => $this->getCustomerClient(), 'status' => 403, 'name' => 'customer'],
            ['client' => $this->getSellerClient(), 'status' => 403, 'name' => 'seller'],
            ['client' => $this->getAdminClient(), 'not_status' => 403, 'name' => 'admin'],
        ];

        $this->checkClients($clients, '/api/level/'.LoadLevelData::LEVEL2_ID.'/activate', [], 'POST');
    }

    /**
     * @test
     */
    public function only_admin_can_edit_level()
    {
        $clients = [
            ['client' => $this->getCustomerClient(), 'status' => 403, 'name' => 'customer'],
            ['client' => $this->getSellerClient(), 'status' => 403, 'name' => 'seller'],
            ['client' => $this->getAdminClient(), 'not_status' => 403, 'name' => 'admin'],
        ];

        $this->checkClients($clients, '/api/level/'.LoadLevelData::LEVEL1_ID, [], 'PUT');
    }

    /**
     * @test
     */
    public function only_admin_and_seller_can_view_level()
    {
        $clients = [
            ['client' => $this->getCustomerClient(), 'status' => 403, 'name' => 'customer'],
            ['client' => $this->getSellerClient(), 'not_status' => 403, 'name' => 'seller'],
            ['client' => $this->getAdminClient(), 'not_status' => 403, 'name' => 'admin'],
        ];

        $this->checkClients($clients, '/api/level/'.LoadLevelData::LEVEL1_ID);
    }

    /**
     * @test
     */
    public function only_admin_can_view_level_customers()
    {
        $clients = [
            ['client' => $this->getCustomerClient(), 'status' => 403, 'name' => 'customer'],
            ['client' => $this->getSellerClient(), 'status' => 403, 'name' => 'seller'],
            ['client' => $this->getAdminClient(), 'not_status' => 403, 'name' => 'admin'],
        ];

        $this->checkClients($clients, '/api/level/'.LoadLevelData::LEVEL1_ID.'/customers');
    }

    /**
     * @test
     */
    public function only_customer_has_access_to_active_levels_list()
    {
        $clients = [
            ['client' => $this->getCustomerClient(), 'not_status' => Response::HTTP_FORBIDDEN, 'name' => 'customer'],
            ['client' => $this->getSellerClient(), 'status' => Response::HTTP_FORBIDDEN, 'name' => 'seller'],
            ['client' => $this->getAdminClient(), 'status' => Response::HTTP_FORBIDDEN, 'name' => 'admin'],
        ];

        $this->checkClients($clients, '/api/customer/level');
    }
}
