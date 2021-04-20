<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Service;

use OpenLoyalty\Bundle\ActivationCodeBundle\Exception\SmsSendException;
use OpenLoyalty\Bundle\SmsApiBundle\Message\Message;

/**
 * Interface SmsSender.
 */
interface SmsSender
{
    /**
     * @param Message $message
     *
     * @throws SmsSendException
     *
     * @return bool
     */
    public function send(Message $message);

    /**
     * @return array
     */
    public function getNeededSettings(): array;

    /**
     * @return bool
     */
    public function hasNeededSettings();

    /**
     * @param array $credentials
     *
     * @return bool
     */
    public function validateCredentials(array $credentials = []): bool;
}
