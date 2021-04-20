<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use OpenLoyalty\Bundle\SettingsBundle\Service\GeneralSettingsManagerInterface;
use OpenLoyalty\Bundle\UserBundle\Service\NotificationService;
use OpenLoyalty\Bundle\UserBundle\Notification\Transport\PushyNotificationTransport;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetails;
use PHPUnit\Framework\TestCase;

/**
 * Class PushyNotificationTransportTest.
 */
class PushyNotificationTransportTest extends TestCase
{
    /**
     * @var GeneralSettingsManagerInterface
     */
    private $settingsManager;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->settingsManager = $this->getMockBuilder(GeneralSettingsManagerInterface::class)->getMock();
        $this->settingsManager->method('getPushySecretKey')
            ->willReturn('00dead11beef22');
    }

    /**
     * @test
     */
    public function it_sends_a_push_notification(): void
    {
        $this->settingsManager->expects($this->once())->method('getPushySecretKey');

        $guzzleClient = $this->getMockBuilder(Client::class)->getMock();
        $guzzleClient->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (Request $request) {
                $body = json_decode((string) $request->getBody(), true);
                $this->assertArrayHasKey('data', $body);
                $this->assertArrayHasKey('to', $body);
                $this->assertEquals('00cafe11de22fabe33', $body['to'][0]);
                $this->assertEquals('https://example.com', $body['data']['url']);
                $this->assertEquals('Test Notification', $body['data']['title']);
                $this->assertEquals('Hello World!', $body['data']['message']);
            }));

        $pushyTransport = new PushyNotificationTransport($guzzleClient, $this->settingsManager);

        $pushNotification = [
            'recipientTokens' => ['00cafe11de22fabe33'],
            'data' => [
                'url' => 'https://example.com',
                'title' => 'Test Notification',
                'message' => 'Hello World!',
            ],
        ];

        $pushyTransport->sendRewardAvailableNotification($pushNotification);
    }

    /**
     * @test
     */
    public function it_does_not_send_an_invitation(): void
    {
        $guzzleClient = $this->getMockBuilder(Client::class)->getMock();
        $guzzleClient->expects($this->never())->method('send');

        $pushyTransport = new PushyNotificationTransport($guzzleClient, $this->settingsManager);

        $invitation = $this->getMockBuilder(InvitationDetails::class)
            ->disableOriginalConstructor()->getMock();

        $notification = new NotificationService();
        $notification->addTransport($pushyTransport);
        $notification->sendInvitation($invitation);
    }
}
