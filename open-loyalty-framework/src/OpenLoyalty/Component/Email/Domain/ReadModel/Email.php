<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Component\Email\Domain\ReadModel;

use Broadway\ReadModel\SerializableReadModel;
use OpenLoyalty\Component\Email\Domain\EmailId;

/**
 * Class Email.
 */
class Email implements SerializableReadModel
{
    /**
     * @var EmailId
     */
    protected $emailId;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $senderName;

    /**
     * @var string
     */
    protected $senderEmail;

    /**
     * @var string|null
     */
    protected $receiverEmail;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * Email constructor.
     *
     * @param EmailId   $emailId
     * @param string    $key
     * @param string    $subject
     * @param string    $content
     * @param string    $senderName
     * @param string    $senderEmail
     * @param \DateTime $updatedAt
     * @param string    $receiverEmail
     */
    public function __construct(
        EmailId $emailId,
        string $key,
        string $subject,
        string $content,
        string $senderName,
        string $senderEmail,
        \DateTime $updatedAt,
        ?string $receiverEmail = null
    ) {
        $this->emailId = $emailId;
        $this->key = $key;
        $this->subject = $subject;
        $this->content = $content;
        $this->senderName = $senderName;
        $this->senderEmail = $senderEmail;
        $this->receiverEmail = $receiverEmail;
        $this->updatedAt = $updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->getEmailId();
    }

    /**
     * @return string
     */
    public function getEmailId(): string
    {
        return $this->emailId->__toString();
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getSenderName(): string
    {
        return $this->senderName;
    }

    /**
     * @return string
     */
    public function getSenderEmail(): string
    {
        return $this->senderEmail;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @return null|string
     */
    public function getReceiverEmail(): ?string
    {
        return $this->receiverEmail;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data): self
    {
        return new self(
            $data['id'],
            $data['key'],
            $data['subject'],
            $data['content'],
            $data['sender_name'],
            $data['sender_email'],
            $data['updated_at'],
            $data['receiver_email'] ?? null
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return [
            'id' => $this->getId(),
            'key' => $this->getKey(),
            'subject' => $this->getSubject(),
            'content' => $this->getContent(),
            'sender_name' => $this->getSenderName(),
            'sender_email' => $this->getSenderEmail(),
            'updated_at' => $this->getUpdatedAt(),
            'receiver_email' => $this->getReceiverEmail(),
        ];
    }
}
