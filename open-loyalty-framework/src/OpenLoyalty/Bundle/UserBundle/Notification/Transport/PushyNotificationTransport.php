<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Notification\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use OpenLoyalty\Bundle\SettingsBundle\Service\GeneralSettingsManagerInterface;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetails;

/**
 * Class PushyNotificationTransport.
 */
class PushyNotificationTransport implements NotificationTransportInterface
{
    private const PUSHY_API_URL = 'https://api.pushy.me/push?api_key=%s';

    /**
     * @var GeneralSettingsManagerInterface
     */
    private $settingsManager;

    /**
     * @var Client
     */
    private $client;

    /**
     * SmsNotificationTransport constructor.
     *
     * @param Client                          $client
     * @param GeneralSettingsManagerInterface $settingsManager
     */
    public function __construct(Client $client, GeneralSettingsManagerInterface $settingsManager)
    {
        $this->client = $client;
        $this->settingsManager = $settingsManager;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sendInvitation(InvitationDetails $invitation): void
    {
        // skip
        return;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    private function sendPushNotification(array $notification): void
    {
        if ($notification['recipientTokens']) {
            try {
                $url = sprintf(self::PUSHY_API_URL, $this->settingsManager->getPushySecretKey());
                $headers = ['Content-Type' => 'application/json'];

                $post = $notification['options'] ?? [];
                $post['to'] = $notification['recipientTokens'];
                $post['data'] = $notification['data'];
                $body = json_encode($post, JSON_UNESCAPED_UNICODE);

                $request = new Request('POST', $url, $headers, $body);

                $this->client->send($request);
            } catch (GuzzleException $e) {
                throw new \Exception($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function sendRewardAvailableNotification(array $notification): void
    {
        if ($notification['recipientTokens']) {
            $this->sendPushNotification($notification);
        }
    }
}
