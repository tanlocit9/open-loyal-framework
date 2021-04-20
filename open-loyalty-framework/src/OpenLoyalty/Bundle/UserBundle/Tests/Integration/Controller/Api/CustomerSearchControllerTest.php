<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Integration\Controller\Api;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;

/**
 * Class CustomerSearchControllerTest.
 */
class CustomerSearchControllerTest extends BaseApiTest
{
    /**
     * @test
     *
     * @dataProvider getPartialPhrases
     *
     * @param string $field
     * @param string $phrase
     * @param int    $count
     */
    public function it_allows_to_find_customers_using_partial_phrase(string $field, string $phrase, int $count): void
    {
        $params = ['search' => [$field => $phrase]];

        $client = $this->createAuthenticatedClient(
            LoadUserData::TEST_SELLER_USERNAME,
            LoadUserData::TEST_SELLER_PASSWORD,
            'seller'
        );
        $client->request('POST', '/api/pos/search/customer', $params);

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');

        $this->assertEquals(
            $count,
            count($data['customers']),
            sprintf('Expected %d records but found %d', $count, count($data['customers']))
        );

        foreach ($data['customers'] as $customer) {
            $this->assertArrayHasKey(
                $field,
                $customer,
                'Field '.$field.' does not exist'
            );
            $this->assertTrue(
                (false !== strpos($customer[$field], $phrase)),
                'Looking for phrase '.$phrase.' but found '.$customer[$field]
            );
        }
    }

    /**
     * @return array
     */
    public function getPartialPhrases(): array
    {
        return [
            ['firstName', 'Marks', 1],
            ['firstName', '1', 1],
            ['firstName', 'John1', 1],
            ['lastName', 'Doe1', 1],
            ['lastName', 'Smith', 2],
            ['phone', '+48456456000', 1],
            ['email', 'user-1', 1],
            ['loyaltyCardNumber', '000000', 3],
        ];
    }
}
