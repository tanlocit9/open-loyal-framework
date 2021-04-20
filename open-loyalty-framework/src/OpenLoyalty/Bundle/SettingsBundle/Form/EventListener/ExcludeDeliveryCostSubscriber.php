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

/**
 * Class ExcludeDeliveryCostSubscriber.
 */
class ExcludeDeliveryCostSubscriber implements EventSubscriberInterface
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
        $excludeDeliveryCosts = $data->getEntry('excludeDeliveryCostsFromTierAssignment');
        if ($excludeDeliveryCosts && $excludeDeliveryCosts->getValue()) {
            $ex = $data->getEntry('excludedDeliverySKUs');

            if (!$ex || !$ex->getValue() || count($ex->getValue()) == 0) {
                $event->getForm()->get('excludedDeliverySKUs')->addError(new FormError((new NotBlank())->message));
            }
        }
    }
}
