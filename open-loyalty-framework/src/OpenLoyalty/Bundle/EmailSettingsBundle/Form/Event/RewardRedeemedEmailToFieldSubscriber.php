<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\EmailSettingsBundle\Form\Event;

use OpenLoyalty\Component\Email\Domain\Email;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RewardRedeemedEmailToFieldSubscriber.
 */
class RewardRedeemedEmailToFieldSubscriber implements EventSubscriberInterface
{
    private const EMAIL_TEMPLATE_KEY = ['OpenLoyaltyUserBundle:email:reward_redeemed.html.twig'];

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [FormEvents::SUBMIT => '__invoke'];
    }

    /**
     * @param FormEvent $event
     */
    public function __invoke(FormEvent $event): void
    {
        /** @var Email $data */
        $data = $event->getData();
        if (null !== $data && in_array($data->getKey(), self::EMAIL_TEMPLATE_KEY)) {
            $receivers = $event->getForm()->get('receiver_email');
            if ($receivers->isEmpty()) {
                $receivers->addError(new FormError((new NotBlank())->message));
            }
        }
    }
}
