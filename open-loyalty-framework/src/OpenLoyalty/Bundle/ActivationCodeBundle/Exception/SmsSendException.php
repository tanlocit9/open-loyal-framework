<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Exception;

use Throwable;

/**
 * Class SmsSendException.
 */
class SmsSendException extends \Exception
{
    /**
     * @var string
     */
    private $recipient;

    /**
     * SmsSendException constructor.
     *
     * @param string         $message
     * @param string         $recipient
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', $recipient, Throwable $previous = null)
    {
        $this->recipient = $recipient;

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }
}
