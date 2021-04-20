<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SmsApiBundle\Message;

/**
 * Class Message.
 */
class Message
{
    /**
     * @var string
     */
    protected $recipient;

    /**
     * @var string
     */
    protected $senderName;

    /**
     * @var string
     */
    protected $content;

    /**
     * @param $recipient
     * @param $senderName
     * @param string $content
     *
     * @return Message
     */
    public static function create($recipient, $senderName, string $content)
    {
        $self = new self();
        $self->recipient = $recipient;
        $self->senderName = $senderName;
        $self->content = $content;

        return $self;
    }

    /**
     * @return string
     */
    public function getRecipient(): string
    {
        return $this->recipient;
    }

    /**
     * @param string $recipient
     */
    public function setRecipient(string $recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @return string
     */
    public function getSenderName(): string
    {
        return $this->senderName;
    }

    /**
     * @param string $senderName
     */
    public function setSenderName(string $senderName)
    {
        $this->senderName = $senderName;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content)
    {
        $this->content = $content;
    }
}
