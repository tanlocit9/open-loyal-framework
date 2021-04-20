<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Tests\Unit\Infrastructure\Service;

use OpenLoyalty\Bundle\EmailBundle\Service\EmailMessageSenderInterface;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Campaign\Infrastructure\Service\CampaignRewardRedeemedEmailSender;
use OpenLoyalty\Component\Campaign\Infrastructure\Service\CampaignRewardRedeemedTemplateParameterCreatorInterface;
use OpenLoyalty\Component\Email\Domain\ReadModel\DoctrineEmailRepositoryInterface;
use OpenLoyalty\Component\Email\Domain\ReadModel\Email;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CampaignRewardRedeemedEmailSenderTest.
 */
final class CampaignRewardRedeemedEmailSenderTest extends TestCase
{
    /**
     * @var DoctrineEmailRepositoryInterface|MockObject
     */
    private $repository;

    /**
     * @var Email|MockObject
     */
    private $email;

    /**
     * @var CampaignRewardRedeemedTemplateParameterCreatorInterface|MockObject
     */
    private $templateParameters;

    /**
     * @var EmailMessageSenderInterface|MockObject
     */
    private $messageSender;

    /**
     * @var CampaignBought|MockObject
     */
    private $campaignBought;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(DoctrineEmailRepositoryInterface::class);
        $this->email = $this->createMock(Email::class);
        $this->messageSender = $this->createMock(EmailMessageSenderInterface::class);
        $this->templateParameters = $this->createMock(CampaignRewardRedeemedTemplateParameterCreatorInterface::class);
        $this->campaignBought = $this->createMock(CampaignBought::class);
    }

    /**
     * @test
     */
    public function it_sends_message_to_receivers_in_email_settings(): void
    {
        $this->email->method('getSubject')->willReturn('Reward Redeemed');
        $this->email->method('getReceiverEmail')->willReturn('jon@oloy.com');

        $this->messageSender->expects($this->once())->method('sendMessage');

        $this->repository->expects($this->once())->method('getByKey')->willReturn($this->email);

        $sender = new CampaignRewardRedeemedEmailSender(
            $this->repository,
            $this->messageSender,
            $this->templateParameters,
            'oloy@oloy.com',
            'oloy@oloy.com'
        );
        $sender->send($this->campaignBought);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_no_settings_found(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->repository->expects($this->once())->method('getByKey')->willReturn(null);

        $provider = new CampaignRewardRedeemedEmailSender(
            $this->repository,
            $this->messageSender,
            $this->templateParameters,
            'oloy@oloy.com',
            'oloy@oloy.com'
        );
        $provider->send($this->campaignBought);
    }

    /**
     * @test
     */
    public function it_not_sends_message_to_receivers_when_no_receiver_settings_are_empty(): void
    {
        $this->email->method('getSubject')->willReturn('Reward Redeemed');
        $this->email->method('getReceiverEmail')->willReturn(null);

        $this->repository->expects($this->once())->method('getByKey')->willReturn($this->email);

        $this->messageSender->expects($this->never())->method('sendMessage');

        $sender = new CampaignRewardRedeemedEmailSender(
            $this->repository,
            $this->messageSender,
            $this->templateParameters,
            'oloy@oloy.com',
            'oloy@oloy.com'
        );
        $sender->send($this->campaignBought);
    }
}
