<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\CampaignBundle\Form\Event;

use OpenLoyalty\Component\Campaign\Domain\Campaign;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class FulfillmentTrackingFieldGiftCampaignTypeSubscriber.
 */
class FulfillmentTrackingFieldGiftCampaignTypeSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [FormEvents::PRE_SUBMIT => '__invoke'];
    }

    /**
     * @param FormEvent $formEvent
     */
    public function __invoke(FormEvent $formEvent): void
    {
        /** @var Campaign $data */
        $data = $formEvent->getData();
        if (isset($data['reward']) && Campaign::REWARD_TYPE_GIFT_CODE === $data['reward']) {
            $formEvent->getForm()->add('fulfillmentTracking', CheckboxType::class);
        }
    }
}
