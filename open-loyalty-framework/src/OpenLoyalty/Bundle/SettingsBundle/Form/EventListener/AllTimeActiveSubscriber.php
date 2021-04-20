<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Form\EventListener;

use OpenLoyalty\Bundle\SettingsBundle\Model\Settings;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class AllTimeActiveSubscriber.
 */
class AllTimeActiveSubscriber implements EventSubscriberInterface
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
        $settings = $data->getEntry('pointsDaysExpiryAfter');
        if ($settings
            && AddPointsTransfer::TYPE_AFTER_X_DAYS === $settings->getValue()
        ) {
            $days = $data->getEntry('pointsDaysActiveCount');
            if (null === $days || empty($days->getValue())) {
                $event
                    ->getForm()
                    ->get('pointsDaysActiveCount')
                    ->addError(new FormError((new NotBlank())->message));
            }
        }
    }
}
