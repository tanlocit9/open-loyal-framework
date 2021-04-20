<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Email\Domain\Command;

use OpenLoyalty\Component\Email\Domain\Email;
use OpenLoyalty\Component\Email\Domain\EmailId;

/**
 * Class UpdateEmail.
 */
final class UpdateEmail extends EmailCommand
{
    /**
     * Email data.
     *
     * @var array
     */
    private $data;

    /**
     * {@inheritdoc}
     *
     * @param array $data
     */
    public function __construct(EmailId $emailId, array $data)
    {
        parent::__construct($emailId);

        $this->validateCommand($data);

        $this->data = $data;
    }

    /**
     * @param EmailId $emailId
     * @param Email   $email
     *
     * @return UpdateEmail
     */
    public static function withEmailEntity(EmailId $emailId, Email $email): self
    {
        $data = [
            'key' => $email->getKey(),
            'subject' => $email->getSubject(),
            'content' => $email->getContent(),
            'sender_name' => $email->getSenderName(),
            'sender_email' => $email->getSenderEmail(),
            'receiver_email' => $email->getReceiverEmail(),
        ];

        return new self($emailId, $data);
    }

    /**
     * Get email data.
     *
     * @return array
     */
    public function getEmailData(): array
    {
        return $this->data;
    }
}
