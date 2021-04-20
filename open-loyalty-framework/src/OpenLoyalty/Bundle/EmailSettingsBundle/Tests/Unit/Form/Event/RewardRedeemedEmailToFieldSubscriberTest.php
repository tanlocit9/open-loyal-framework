<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\EmailSettingsBundle\Tests\Unit\Form\Event;

use OpenLoyalty\Bundle\EmailSettingsBundle\Form\Event\RewardRedeemedEmailToFieldSubscriber;
use OpenLoyalty\Component\Email\Domain\Email;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

/**
 * Class RewardRedeemedEmailToFieldSubscriberTest.
 */
final class RewardRedeemedEmailToFieldSubscriberTest extends TestCase
{
    /**
     * @var RewardRedeemedEmailToFieldSubscriber
     */
    private $listener;

    /**
     * @var FormInterface|MockObject
     */
    private $form;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->form = $this->createMock(FormInterface::class);

        $this->listener = new RewardRedeemedEmailToFieldSubscriber();
    }

    /**
     * @test
     */
    public function it_adds_error_to_receiver_emails_field_is_empty_when_reward_redeemed_template_is_edited(): void
    {
        $emailEntity = $this->createMock(Email::class);
        $emailEntity->method('getKey')->willReturn('OpenLoyaltyUserBundle:email:reward_redeemed.html.twig');

        $receivers = $this->createMock(FormInterface::class);
        $receivers->expects($this->once())->method('addError');
        $receivers->expects($this->once())->method('isEmpty')->willReturn(true);

        $this->form->expects($this->once())->method('get')->willReturn($receivers);

        $this->listener->__invoke(new FormEvent($this->form, $emailEntity));
    }

    /**
     * @test
     */
    public function it_not_adds_error_to_receiver_emails_field_is_not_empty_when_reward_redeemed_template_is_edited(): void
    {
        $emailEntity = $this->createMock(Email::class);
        $emailEntity->method('getKey')->willReturn('OpenLoyaltyUserBundle:email:reward_redeemed.html.twig');

        $receivers = $this->createMock(FormInterface::class);
        $receivers->expects($this->never())->method('addError');
        $receivers->expects($this->once())->method('isEmpty')->willReturn(false);

        $this->form->expects($this->once())->method('get')->willReturn($receivers);

        $this->listener->__invoke(new FormEvent($this->form, $emailEntity));
    }

    /**
     * @test
     */
    public function it_not_adds_error_when_not_edited_reward_redeemed_template(): void
    {
        $emailEntity = $this->createMock(Email::class);
        $emailEntity->method('getKey')->willReturn('OpenLoyaltyUserBundle:email:register.html.twig');

        $receivers = $this->createMock(FormInterface::class);
        $receivers->expects($this->never())->method('addError');
        $receivers->expects($this->never())->method('isEmpty')->willReturn(false);

        $this->form->expects($this->never())->method('get')->willReturn($receivers);

        $this->listener->__invoke(new FormEvent($this->form, $emailEntity));
    }
}
