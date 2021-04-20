<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Form\EventListener;

use OpenLoyalty\Bundle\SettingsBundle\Model\Settings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class AllTimeNotLockedSubscriber.
 */
class AllTimeNotLockedSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [FormEvents::SUBMIT => 'submit'];
    }

    /**
     * @param FormEvent $event
     */
    public function submit(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data instanceof Settings) {
            return;
        }
        $allTime = $data->getEntry('allTimeNotLocked');
        if (!$allTime || !$allTime->getValue()) {
            $days = $data->getEntry('pointsDaysLocked');
            if (!$days || !$days->getValue()) {
                $event->getForm()->get('pointsDaysLocked')->addError(new FormError((new NotBlank())->message));
            }
            if ($days && $days->getValue() < 0) {
                $minMessage = (new Range(['min' => 0]))->minMessage;
                $minMessage = str_replace('{{ limit }}', 0, $minMessage);
                $event->getForm()->get('pointsDaysLocked')->addError(new FormError($minMessage));
            }
        }
    }
}
