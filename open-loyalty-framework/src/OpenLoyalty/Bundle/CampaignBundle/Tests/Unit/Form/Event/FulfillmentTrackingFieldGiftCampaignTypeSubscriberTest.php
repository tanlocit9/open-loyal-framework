<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\CampaignBundle\Tests\Unit\Form\Event;

use OpenLoyalty\Bundle\CampaignBundle\Form\Event\FulfillmentTrackingFieldGiftCampaignTypeSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

/**
 * Class CampaignPhotoFormTypeTest.
 */
final class FulfillmentTrackingFieldGiftCampaignTypeSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function it_adds_fulfillment_tracking_checkbox_when_reward_is_gift_code(): void
    {
        /** @var FormInterface|MockObject $form */
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('add');

        $subscriber = new FulfillmentTrackingFieldGiftCampaignTypeSubscriber();
        $subscriber->__invoke(new FormEvent($form, ['reward' => 'gift_code']));
    }

    /**
     * @test
     */
    public function it_not_adds_fulfillment_tracking_checkbox_when_reward_is_not_gift_code(): void
    {
        /** @var FormInterface|MockObject $form */
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->never())->method('add');

        $subscriber = new FulfillmentTrackingFieldGiftCampaignTypeSubscriber();
        $subscriber->__invoke(new FormEvent($form, ['reward' => 'free_delivery_code']));
    }
}
