<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\EmailBundle\DTO;

/**
 * Class EmailParameter.
 */
class EmailParameter
{
    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @var string
     */
    private $senderName;

    /**
     * @var string
     */
    private $recipientEmail;

    /**
     * @var string
     */
    private $subject;

    /**
     * EmailParameter constructor.
     *
     * @param string $senderEmail
     * @param string $senderName
     * @param string $recipientEmail
     * @param string $subject
     */
    public function __construct(
        string $senderEmail,
        string $senderName,
        string $recipientEmail,
        string $subject
    ) {
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
        $this->recipientEmail = $recipientEmail;
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function senderEmail(): string
    {
        return $this->senderEmail;
    }

    /**
     * @return string
     */
    public function senderName(): string
    {
        return $this->senderName;
    }

    /**
     * @return string
     */
    public function recipientEmail(): string
    {
        return $this->recipientEmail;
    }

    /**
     * @return string
     */
    public function subject(): string
    {
        return $this->subject;
    }
}
