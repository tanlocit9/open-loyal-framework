<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SmsApiBundle\SmsApi;

use OpenLoyalty\Bundle\ActivationCodeBundle\Exception\SmsSendException;
use OpenLoyalty\Bundle\ActivationCodeBundle\Service\SmsSender;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Bundle\SmsApiBundle\Message\Message;
use Psr\Log\LoggerInterface;
use SMSApi\Api\SmsFactory;
use SMSApi\Client;
use SMSApi\Exception\SmsapiException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class OloySmsApi.
 */
class OloySmsApi implements SmsSender
{
    const FIELD_NAME = 'smsApiToken';

    /**
     * SMS API Code.
     */
    const GATEWAY_CODE = 'sms_api';

    /**
     * @var SettingsManager
     */
    protected $settingsManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * OloySmsApi constructor.
     *
     * @param SettingsManager $settingsManager
     * @param LoggerInterface $logger
     */
    public function __construct(SettingsManager $settingsManager, LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->settingsManager = $settingsManager;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getNeededSettings(): array
    {
        return [self::FIELD_NAME => 'text'];
    }

    /**
     * {@inheritdoc}
     */
    public function hasNeededSettings()
    {
        $token = $this->settingsManager->getSettingByKey('smsApiToken');
        if (!$token || !$token->getValue()) {
            return false;
        }

        return true;
    }

    /**
     * @param string|null $token
     *
     * @return Client
     */
    private function getClient(string $token = null)
    {
        if (null === $token) {
            $token = $this->settingsManager->getSettingByKey('smsApiToken');
            if (!$token || !$token->getValue()) {
                return;
            }

            $token = $token->getValue();
        }

        return Client::createFromToken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function send(Message $message)
    {
        $smsapi = new SmsFactory();
        $client = $this->getClient();
        if (!$client) {
            return false;
        }
        $smsapi->setClient($client);

        try {
            $actionSend = $smsapi->actionSend();

            $actionSend->setTo($message->getRecipient());
            $actionSend->setText($message->getContent());
            $actionSend->setSender($message->getSenderName());

            $response = $actionSend->execute();

            if ($response->getLength() < 1) {
                return false;
            }

            foreach ($response->getList() as $status) {
                return in_array($status->getStatus(), [
                    'DELIVERED',
                    'SENT',
                    'PENDING',
                    'QUEUE',
                    'ACCEPTED',
                    'RENEWAL',
                ]);
            }
        } catch (SmsapiException $e) {
            $this->logger->error($this->translator->trans('Send sms failed: '.$e->getMessage()), ['exception' => $e]);

            throw new SmsSendException($this->translator->trans('Send sms failed: '.$e->getMessage()), $message->getRecipient(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateCredentials(array $credentials = []): bool
    {
        $user = (isset($credentials[self::FIELD_NAME])) ? $credentials[self::FIELD_NAME] : null;

        $smsapi = new SmsFactory();
        $client = $this->getClient($user);
        if (!$client) {
            return false;
        }
        $smsapi->setClient($client);

        try {
            $actionSend = $smsapi->actionSend();

            $actionSend->setTo('48000000000');
            $actionSend->setText('test');
            $actionSend->setSender('test');
            $actionSend->setTest(1);

            $response = $actionSend->execute();

            if ($response->getLength() < 1) {
                return false;
            }

            foreach ($response->getList() as $status) {
                return in_array($status->getStatus(), [
                    'DELIVERED',
                    'SENT',
                    'PENDING',
                    'QUEUE',
                    'ACCEPTED',
                    'RENEWAL',
                ]);
            }
        } catch (SmsapiException $e) {
            return false;
        }

        return true;
    }
}
