<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\WorldTextBundle\Service;

use OpenLoyalty\Bundle\ActivationCodeBundle\Exception\SmsSendException;
use OpenLoyalty\Bundle\ActivationCodeBundle\Service\SmsSender;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Bundle\SmsApiBundle\Message\Message;
use OpenLoyalty\Bundle\WorldTextBundle\Lib\Exception\WTException;
use OpenLoyalty\Bundle\WorldTextBundle\Lib\WorldTextSms;
use Psr\Log\LoggerInterface;

/**
 * Class WorldTextSender.
 */
class WorldTextSender implements SmsSender
{
    const FIELD_USER_ID = 'smsAccountId';
    const FIELD_KEY = 'smsApiKey';

    /**
     * SMS API Code.
     */
    const GATEWAY_CODE = 'world_text';

    /**
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * WorldTextSender constructor.
     *
     * @param SettingsManager $settingsManager
     * @param LoggerInterface $logger
     */
    public function __construct(SettingsManager $settingsManager, LoggerInterface $logger)
    {
        $this->settingsManager = $settingsManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Message $message)
    {
        $client = $this->getClient();
        if (!$client) {
            $this->logger->error('Send sms failed. Sms settings does not exist');

            return false;
        }
        try {
            $phone = ltrim($message->getRecipient(), '+');
            $client->send($phone, $message->getContent(), $message->getSenderName());
        } catch (WTException $e) {
            $this->logger->error('Send sms failed: '.$e->getMessage(), [
                'exception' => $e, 'phone' => $message->getRecipient(),
                'desc' => $e->getDesc(),
            ]);

            throw new SmsSendException('Send sms failed: '.$e->getMessage(), $message->getRecipient(), $e);
        }

        return true;
    }

    /**
     * @param string|null $smsId
     * @param string|null $key
     *
     * @return WorldTextSms|null
     */
    private function getClient($smsId = null, $key = null)
    {
        if (null === $smsId && null === $key) {
            $smsId = $this->settingsManager->getSettingByKey('smsAccountId');
            $key = $this->settingsManager->getSettingByKey('smsApiKey');

            if (!$smsId || !$smsId->getValue() || !$key || !$key->getValue()) {
                return;
            }

            $smsId = $smsId->getValue();
            $key = $key->getValue();
        }

        return WorldTextClientFactory::create($smsId, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function hasNeededSettings()
    {
        $smsId = $this->settingsManager->getSettingByKey('smsAccountId');
        $key = $this->settingsManager->getSettingByKey('smsApiKey');

        if (!$smsId || !$smsId->getValue() || !$key || !$key->getValue()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getNeededSettings(): array
    {
        return [
            self::FIELD_USER_ID => 'text',
            self::FIELD_KEY => 'text',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validateCredentials(array $credentials = []): bool
    {
        $user = isset($credentials[self::FIELD_USER_ID]) ? $credentials[self::FIELD_USER_ID] : null;
        $key = isset($credentials[self::FIELD_KEY]) ? $credentials[self::FIELD_KEY] : null;

        $client = $this->getClient($user, $key);
        if (!$client) {
            $this->logger->error('Send sms failed. Sms settings does not exist');

            return false;
        }
        try {
            $client->send('+48000000000', 'test', 'test', null, true);
        } catch (WTException $e) {
            return false;
        }

        return true;
    }
}
