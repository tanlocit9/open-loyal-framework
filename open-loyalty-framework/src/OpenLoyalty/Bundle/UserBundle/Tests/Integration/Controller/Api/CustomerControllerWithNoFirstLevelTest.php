<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\LevelBundle\DataFixtures\ORM\LoadLevelData;
use OpenLoyalty\Component\Customer\Infrastructure\LevelDowngradeModeProvider;

/**
 * Class CustomerControllerWithNoFirstLevelTest.
 */
final class CustomerControllerWithNoFirstLevelTest extends BaseApiTest
{
    /**
     * @var string
     */
    protected static $levelCustomerId;

    /**
     * Make first level inactive for these tests.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::bootKernel();

        $client = static::createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/level/'.LoadLevelData::LEVEL0_ID.'/activate',
            [
                'active' => false,
            ]
        );
        $client->request(
            'POST',
            '/api/level/'.LoadLevelData::LEVEL2_ID.'/activate',
            [
                'active' => true,
            ]
        );

        $settingsManager = static::$kernel->getContainer()->get('ol.settings.manager');
        $settings = $settingsManager->getSettings();

        $tierAssignType = $settingsManager->getSettingByKey('tierAssignType');
        $tierAssignType->setValue('points');
        $levelDowngradeMode = $settingsManager->getSettingByKey('levelDowngradeMode');
        $levelDowngradeMode->setValue(LevelDowngradeModeProvider::MODE_X_DAYS);
        $levelDowngradeBase = $settingsManager->getSettingByKey('levelDowngradeBase');
        $levelDowngradeBase->setValue(LevelDowngradeModeProvider::BASE_EARNED_POINTS);

        $allowProfileEditSettingsEntry = $settingsManager->getSettingByKey('allowCustomersProfileEdits');
        $allowProfileEditSettingsEntry->setValue(true);

        $settings->addEntry($allowProfileEditSettingsEntry);
        $settings->addEntry($tierAssignType);
        $settings->addEntry($levelDowngradeMode);
        $settings->addEntry($levelDowngradeBase);

        $settingsManager->save($settings);
    }

    /**
     * @test
     */
    public function it_allows_to_register_new_customer_when_level_is_selected(): void
    {
        // try to register the user
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/customer/register',
            [
                'customer' => [
                    'firstName' => 'David',
                    'lastName' => 'WithLevel',
                    'levelId' => LoadLevelData::LEVEL2_ID,
                    'email' => 'david-with-level@test.example',
                    'gender' => 'male',
                    'birthDate' => '1990-01-01',
                    'address' => [
                        'street' => 'Domaniewska',
                        'address1' => '12/34',
                        'postal' => '02-468',
                        'city' => 'Warszawa',
                        'country' => 'PL',
                        'province' => 'mazowieckie',
                    ],
                    'agreement1' => true,
                    'loyaltyCardNumber' => '9812738122',
                ],
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200'.$response->getContent());
        $this->assertArrayHasKey('customerId', $data);
        $this->assertArrayHasKey('email', $data);

        self::$levelCustomerId = $data['customerId'];
    }

    /**
     * @test
     */
    public function it_does_not_allow_to_register_new_customer_when_no_constraints_level_is_deactivated(): void
    {
        // try to register the user
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/customer/register',
            [
                'customer' => [
                    'firstName' => 'David',
                    'lastName' => 'WithoutLevel',
                    'email' => 'david-wo-level@test.example',
                    'gender' => 'male',
                    'birthDate' => '1990-01-01',
                    'address' => [
                        'street' => 'Domaniewska',
                        'address1' => '12/34',
                        'postal' => '02-468',
                        'city' => 'Warszawa',
                        'country' => 'PL',
                        'province' => 'mazowieckie',
                    ],
                    'agreement1' => true,
                    'loyaltyCardNumber' => '9812738133',
                ],
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode(), 'Response should have status 400');
        $this->assertEquals('Neither level is not available as start level for this customer.', $data['form']['children']['levelId']['errors'][0]);
    }

    /**
     * @test
     * @depends it_allows_to_register_new_customer_when_level_is_selected
     */
    public function it_allows_to_edit_level_of_customer_who_has_no_level(): void
    {
        $client = $this->createAuthenticatedClient();

        $customerData['levelId'] = LoadLevelData::LEVEL3_ID;

        $client->request(
            'PUT',
            '/api/customer/'.self::$levelCustomerId,
            [
                'customer' => $customerData,
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $client->request(
            'GET',
            '/api/customer/'.self::$levelCustomerId
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertEquals($customerData['levelId'], $data['level']['levelId']['id']);
        $this->assertEquals($customerData['levelId'], $data['levelId']);
        $this->assertEquals($customerData['levelId'], $data['manuallyAssignedLevelId']['levelId']);
    }

    /**
     * @test
     * @depends it_allows_to_edit_level_of_customer_who_has_no_level
     */
    public function it_allows_to_remove_customers_level_when_no_constraints_level_is_deactivated(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/customer/'.self::$levelCustomerId.'/remove-manually-level',
            []
        );

        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode(), 'Response should have status 204'.$response->getContent());
    }

    /**
     * @test
     * @depends it_allows_to_remove_customers_level_when_no_constraints_level_is_deactivated
     */
    public function it_allows_no_level_customer_to_pass_to_an_active_level(): void
    {
        $client = $this->createAuthenticatedClient();

        $transferData = [
            'customer' => self::$levelCustomerId,
            'points' => 201,
        ];

        $client->request(
            'POST',
            '/api/points/transfer/add',
            [
                'transfer' => $transferData,
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $client->request(
            'GET',
            '/api/customer/'.self::$levelCustomerId
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('levelId', $data, 'Response should include levelId key: '.$response->getContent());
        $this->assertArrayHasKey('level', $data, 'Response should include level key: '.$response->getContent());
        $this->assertEquals(LoadLevelData::LEVEL2_ID, $data['level']['levelId']['id'], $response->getContent());
        $this->assertEquals(LoadLevelData::LEVEL2_ID, $data['levelId']);
        $this->assertArrayNotHasKey('manuallyAssignedLevelId', $data);
    }

    /**
     * Make first level active for the rest of tests.
     */
    public static function tearDownAfterClass(): void
    {
        $client = static::createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/level/'.LoadLevelData::LEVEL0_ID.'/activate',
            [
                'active' => true,
            ]
        );

        $settingsManager = static::$kernel->getContainer()->get('ol.settings.manager');
        $settings = $settingsManager->getSettings();

        $tierAssignType = $settingsManager->getSettingByKey('tierAssignType');
        $tierAssignType->setValue('transactions');

        $settings->addEntry($tierAssignType);
        $settingsManager->save($settings);

        parent::tearDownAfterClass();
    }
}
