<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Tests\Integration\Controller\Api;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;
use OpenLoyalty\Bundle\UtilityBundle\Tests\Integration\Traits\UploadedFileTrait;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;

/**
 * Class CustomerPointsTransferControllerTest.
 */
class CustomerPointsTransferControllerTest extends BaseApiTest
{
    use UploadedFileTrait;

    /**
     * @test
     */
    public function it_fetches_transfer(): void
    {
        $client = $this->createAuthenticatedClient(
            LoadUserData::USER_TRANSFER_3_USERNAME,
            LoadUserData::USER_TRANSFER_3_PASSWORD,
            'customer'
        );
        $client->request(
            'GET',
            '/api/customer/points/transfer'
        );

        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertGreaterThanOrEqual(1, count($data['transfers']));
    }

    /**
     * @test
     */
    public function it_transfers_points(): void
    {
        $client = $this->createAuthenticatedClient(
            LoadUserData::USER_TRANSFER_3_USERNAME,
            LoadUserData::USER_TRANSFER_3_PASSWORD,
            'customer'
        );
        $senderAccount = $this->getAccountIdByCustomerId(LoadUserData::USER_TRANSFER_3_USER_ID);
        $receiverAccount = $this->getAccountIdByCustomerId(LoadUserData::USER_TRANSFER_1_USER_ID);
        $senderAmount = $senderAccount->getAvailableAmount();
        $receiverAmount = $receiverAccount->getAvailableAmount();

        $client->request(
            'POST',
            '/api/customer/points/p2p-transfer',
            [
                'transfer' => [
                    'receiver' => LoadUserData::USER_TRANSFER_1_USER_ID,
                    'points' => 100,
                ],
            ]
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey('pointsTransferId', $data);
        $senderAccount = $this->getAccountIdByCustomerId(LoadUserData::USER_TRANSFER_3_USER_ID);
        $receiverAccount = $this->getAccountIdByCustomerId(LoadUserData::USER_TRANSFER_1_USER_ID);

        $this->assertEquals($senderAmount - 100, $senderAccount->getAvailableAmount());
        $this->assertEquals($receiverAmount + 100, $receiverAccount->getAvailableAmount());
    }

    /**
     * @param $customerId
     *
     * @return AccountDetails
     */
    protected function getAccountIdByCustomerId($customerId): AccountDetails
    {
        /** @var Repository $repo */
        $repo = self::$kernel->getContainer()->get('oloy.points.account.repository.account_details');
        $accounts = $repo->findBy(['customerId' => $customerId]);
        /** @var AccountDetails $account */
        $account = reset($accounts);

        return $account;
    }
}
