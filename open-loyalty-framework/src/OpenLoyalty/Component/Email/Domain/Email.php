<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Component\Email\Domain;

use Assert\Assertion as Assert;

/**
 * Class Email.
 */
class Email
{
    /**
     * @var EmailId
     */
    private $emailId;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $senderName;

    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @var null|string
     */
    private $receiverEmail = null;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * Email constructor.
     *
     * @param EmailId $emailId
     * @param string  $key
     * @param string  $subject
     * @param string  $content
     * @param string  $senderName
     * @param string  $senderEmail
     * @param string  $receiverEmail
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(
        EmailId $emailId,
        string $key,
        string $subject,
        string $content,
        string $senderName,
        string $senderEmail,
        string $receiverEmail = null
    ) {
        Assert::uuid((string) $emailId);
        Assert::notEmpty($key);
        Assert::notEmpty($subject);
        Assert::notEmpty($content);
        Assert::notEmpty($senderName);
        Assert::email($senderEmail);

        $this->emailId = $emailId;
        $this->key = $key;
        $this->subject = $subject;
        $this->content = $content;
        $this->senderName = $senderName;
        $this->senderEmail = $senderEmail;
        $this->updatedAt = new \DateTime('now');
        $this->receiverEmail = $receiverEmail;
    }

    /**
     * Create email instance.
     *
     * @param EmailId $id
     * @param array   $data
     *
     * @return Email
     *
     * @throws \Assert\AssertionFailedException
     */
    public static function create(EmailId $id, array $data): self
    {
        return new self(
            $id,
            $data['key'],
            $data['subject'],
            $data['content'],
            $data['sender_name'],
            $data['sender_email'],
            $data['receiver_email'] ?? null
        );
    }

    /**
     * @return EmailId
     */
    public function getEmailId(): EmailId
    {
        return $this->emailId;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @param string $senderName
     */
    public function setSenderName(string $senderName): void
    {
        $this->senderName = $senderName;
    }

    /**
     * @param string $senderEmail
     */
    public function setSenderEmail(string $senderEmail): void
    {
        $this->senderEmail = $senderEmail;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @param null|string $receiverEmail
     */
    public function setReceiverEmail(?string $receiverEmail): void
    {
        $this->receiverEmail = $receiverEmail;
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
     * @return null|string
     */
    public function getReceiverEmail(): ?string
    {
        return $this->receiverEmail;
    }
}
