<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Form\EventListener;

use OpenLoyalty\Bundle\ActivationCodeBundle\Service\SmsSender;
use OpenLoyalty\Bundle\SettingsBundle\Entity\StringSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Model\Settings;
use OpenLoyalty\Bundle\SettingsBundle\Validator\Constraints\NotEmptyValue;
use OpenLoyalty\Component\Customer\Domain\Model\AccountActivationMethod;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class ActivationMethodSubscriber.
 */
class ActivationMethodSubscriber implements EventSubscriberInterface
{
    /**
     * @var SmsSender|null
     */
    private $smsGateway;

    /**
     * ActivationMethodSubscriber constructor.
     *
     * @param SmsSender|null $smsSender
     */
    public function __construct(SmsSender $smsSender = null)
    {
        $this->smsGateway = $smsSender;
    }

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
        /** @var StringSettingEntry $activationMethod */
        $activationMethod = $data->getEntry('accountActivationMethod');
        if (empty($activationMethod->getValue())) {
            $event->getForm()->get('accountActivationMethod')->addError(new FormError((new NotEmptyValue())->message));
        }

        if (!$data instanceof Settings ||
            null === $this->smsGateway ||
            $activationMethod->getValue() !== AccountActivationMethod::METHOD_SMS
        ) {
            return;
        }

        $areCredentialsValid = $this->smsGateway->validateCredentials($data->toArray());
        if (false === $areCredentialsValid) {
            $event->getForm()->get('accountActivationMethod')->addError(new FormError('Bad credentials'));
        }
    }
}
