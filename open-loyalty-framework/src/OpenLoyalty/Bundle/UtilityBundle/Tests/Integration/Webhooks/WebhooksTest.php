<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UtilityBundle\Tests\Integration\Webhooks;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseApiTest;
use OpenLoyalty\Bundle\UtilityBundle\Provider\DefaultWebhookConfigProvider;
use OpenLoyalty\Component\Webhook\Infrastructure\Client\DefaultWebhookClient;
use OpenLoyalty\Component\Webhook\Infrastructure\Client\WebhookClientInterface;
use OpenLoyalty\Component\Webhook\Infrastructure\WebhookConfigProvider;
use Symfony\Bundle\FrameworkBundle\Client;
use OpenLoyalty\Component\Customer\Infrastructure\SystemEvent\Listener\WebhookListener as CustomerWebhookListener;
use OpenLoyalty\Component\Account\Infrastructure\SystemEvent\Listener\WebhookListener as AccountWebhookListener;
use OpenLoyalty\Component\Transaction\Infrastructure\SystemEvent\Listener\WebhookListener as TransactionWebhookListener;

/**
 * Class WebhooksTest.
 */
class WebhooksTest extends BaseApiTest
{
    /**
     * @param array $types
     *
     * @return Client
     */
    protected function prepareClient(array &$types)
    {
        $client = $this->createAuthenticatedClient();
        $configProviderMock = $this->getMockBuilder(WebhookConfigProvider::class)->getMock();
        $configProviderMock->method('isEnabled')->willReturn(true);
        $client->getContainer()->set(DefaultWebhookConfigProvider::class, $configProviderMock);

        $webhookClientMock = $this->getMockBuilder(WebhookClientInterface::class)->getMock();
        $webhookClientMock->expects($this->any())->method('postAction')
            ->willReturnCallback(function ($uri, $data) use (&$types) {
                $types[$data['type']] = true;
            });
        $client->getContainer()->set(DefaultWebhookClient::class, $webhookClientMock);

        return $client;
    }

    /**
     * @test
     */
    public function it_calls_webhook_events_during_customer_registration()
    {
        $types = [];
        $client = $this->prepareClient($types);

        $client->request(
            'POST',
            '/api/customer/register',
            [
                'customer' => [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => rand(0, 1000).'test@doe.com',
                    'gender' => 'male',
                    'birthDate' => '1990-01-01',
                    'address' => [
                        'street' => 'Bagno',
                        'address1' => '12',
                        'postal' => '00-800',
                        'city' => 'Warszawa',
                        'country' => 'PL',
                        'province' => 'mazowieckie',
                    ],
                    'agreement1' => true,
                    'agreement2' => true,
                ],
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey(
            CustomerWebhookListener::CUSTOMER_REGISTERED_WEBHOOK_TYPE,
            $types,
            'customer.registered has not been invoked'
        );
        $this->assertArrayHasKey(
            CustomerWebhookListener::CUSTOMER_UPDATED_WEBHOOK_TYPE,
            $types,
            'customer.updated has not been invoked'
        );
        $this->assertArrayHasKey(
            CustomerWebhookListener::CUSTOMER_LEVEL_CHANGED_WEBHOOK_TYPE,
            $types,
            'customer.level_changed has not been invoked'
        );
        $this->assertArrayHasKey(
            AccountWebhookListener::ACCOUNT_AVAILABLE_POINTS_AMOUNT_CHANGED_WEBHOOK_TYPE,
            $types,
            'account.available_points_amount_changed has not invoked'
        );
    }

    /**
     * @test
     */
    public function it_calls_webhook_event_after_transaction_is_registered()
    {
        $types = [];
        $client = $this->prepareClient($types);

        $client->request(
            'POST',
            '/api/transaction',
            [
                'transaction' => [
                    'transactionData' => [
                        'documentNumber' => '12311111',
                        'documentType' => 'sell',
                        'purchaseDate' => '2015-01-01',
                        'purchasePlace' => 'wroclaw',
                    ],
                    'items' => [
                        0 => [
                            'sku' => ['code' => '123'],
                            'name' => 'sku',
                            'quantity' => 1,
                            'grossValue' => 1,
                            'category' => 'test',
                            'maker' => 'company',
                            'labels' => [
                                [
                                    'key' => 'test',
                                    'value' => 'label',
                                ],
                            ],
                        ],
                    ],
                    'customerData' => [
                        'name' => 'Jan Nowak',
                        'email' => 'user-temp2@oloy.com',
                        'nip' => 'aaa',
                        'loyaltyCardNumber' => 'not-present-in-system',
                        'address' => [
                            'street' => 'Bagno',
                            'address1' => '12',
                            'city' => 'Warszawa',
                            'country' => 'PL',
                            'province' => 'Mazowieckie',
                            'postal' => '00-800',
                        ],
                    ],
                ],
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'Response should have status 200');
        $this->assertArrayHasKey(
            TransactionWebhookListener::TRANSACTION_REGISTERED_WEBHOOK_TYPE,
            $types,
                'transaction.registered has not been invoked'
        );
    }
}
