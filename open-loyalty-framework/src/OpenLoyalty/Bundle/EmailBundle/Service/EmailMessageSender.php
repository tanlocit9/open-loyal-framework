<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\EmailBundle\Service;

use OpenLoyalty\Bundle\EmailBundle\Mailer\OloyMailer;
use OpenLoyalty\Bundle\EmailBundle\DTO\EmailParameter;
use OpenLoyalty\Bundle\EmailBundle\DTO\EmailTemplateParameter;

/**
 * Class RewardRedeemedEmailSender.
 */
class EmailMessageSender implements EmailMessageSenderInterface
{
    /**
     * @var MessageFactoryInterface
     */
    private $messageFactory;

    /**
     * @var OloyMailer
     */
    private $mailer;

    /**
     * RewardRedeemedEmailSender constructor.
     *
     * @param MessageFactoryInterface $messageFactory
     * @param OloyMailer              $mailer
     */
    public function __construct(
        MessageFactoryInterface $messageFactory,
        OloyMailer $mailer
    ) {
        $this->messageFactory = $messageFactory;
        $this->mailer = $mailer;
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessage(EmailParameter $emailParameter, EmailTemplateParameter $templateParameter): bool
    {
        $message = $this->messageFactory->create();

        $message->setSubject($emailParameter->subject());
        $message->setRecipientName($emailParameter->recipientEmail());
        $message->setRecipientEmail($emailParameter->recipientEmail());
        $message->setSenderEmail($emailParameter->senderEmail());
        $message->setSenderName($emailParameter->senderName());

        $message->setTemplate($templateParameter->template());
        $message->setParams($templateParameter->parameters());

        return $this->mailer->send($message);
    }
}
