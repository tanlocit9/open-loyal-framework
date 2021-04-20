<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Service;

use OpenLoyalty\Bundle\SmsApiBundle\Message\Message;
use Psr\Log\LoggerInterface;

/**
 * Class DummySmsApi.
 */
class DummySmsApi implements SmsSender
{
    /**
     * SMS API Code.
     */
    const GATEWAY_CODE = 'dummy';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DummySmsApi constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Message $message)
    {
        if (!$this->logger) {
            return false;
        }
        $this->logger->info('[SMS]', [
            'sender' => $message->getSenderName(),
            'recipient' => $message->getRecipient(),
            'content' => $message->getContent(),
        ]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getNeededSettings(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function hasNeededSettings()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateCredentials(array $credentials = []): bool
    {
        return true;
    }
}
