<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Email\Domain\Command;

use Assert\Assertion as Assert;
use OpenLoyalty\Component\Email\Domain\EmailId;

/**
 * Class EmailCommand.
 */
class EmailCommand
{
    /**
     * @var EmailId
     */
    protected $emailId;

    /**
     * EmailCommand constructor.
     *
     * @param EmailId $emailId
     */
    public function __construct(EmailId $emailId)
    {
        $this->emailId = $emailId;
    }

    /**
     * Get email id.
     *
     * @return EmailId
     */
    public function getEmailId()
    {
        return $this->emailId;
    }

    /**
     * Validate command.
     *
     * @param array $data
     */
    protected function validateCommand(array $data)
    {
        Assert::uuid($this->emailId->__toString());
        Assert::notEmpty($data['key']);
        Assert::notEmpty($data['subject']);
        Assert::notEmpty($data['content']);
        Assert::notEmpty($data['sender_name']);
        Assert::notEmpty($data['sender_email']);
    }
}
