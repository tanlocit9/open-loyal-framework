<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\EmailBundle\Tests\Unit\Service;

use OpenLoyalty\Bundle\EmailBundle\DTO\EmailParameter;
use OpenLoyalty\Bundle\EmailBundle\DTO\EmailTemplateParameter;
use OpenLoyalty\Bundle\EmailBundle\Mailer\OloyMailer;
use OpenLoyalty\Bundle\EmailBundle\Service\EmailMessageSender;
use OpenLoyalty\Bundle\EmailBundle\Service\MessageFactory;
use OpenLoyalty\Bundle\EmailBundle\Service\MessageFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class EmailMessageSenderTest.
 */
final class EmailMessageSenderTest extends TestCase
{
    /**
     * @var MessageFactoryInterface
     */
    private $messageFactory;

    /**
     * @var OloyMailer|MockObject
     */
    private $mailer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->messageFactory = new MessageFactory();

        $this->mailer = $this->createMock(OloyMailer::class);
        $this->mailer->method('send')->willReturn(true);
    }

    /**
     * @test
     */
    public function it_sends_message_to_recipient(): void
    {
        $sender = new EmailMessageSender($this->messageFactory, $this->mailer);

        $emailParameter = new EmailParameter('oloy@oloy.com', 'Oloy', 'recipient@oloy.com', 'Subject');
        $templateParameter = new EmailTemplateParameter('example.html.twig');
        $templateParameter->addParameter('user_name', 'Jon Don');

        $send = $sender->sendMessage($emailParameter, $templateParameter);
        $this->assertTrue($send);
    }
}
