<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Form\Listener;

use OpenLoyalty\Bundle\SettingsBundle\Form\Type\SettingsFormType;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AllowUserToEditProfileSubscriber.
 */
final class AllowUserToEditProfileSubscriber implements EventSubscriberInterface
{
    private const FORM_FIELDS_ALLOWED_TO_EDIT = [
        'agreement1',
        'agreement2',
        'agreement3',
        'createdAt',
        'referral_customer_email',
        'posId',
        'sellerId',
        'levelId',
    ];

    /**
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * AllowUserToEditProfileSubscriber constructor.
     *
     * @param SettingsManager       $settingsManager
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface   $translator
     */
    public function __construct(
        SettingsManager $settingsManager,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator
    ) {
        $this->settingsManager = $settingsManager;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [FormEvents::POST_SUBMIT => '__invoke'];
    }

    /**
     * @param FormEvent $event
     */
    public function __invoke(FormEvent $event): void
    {
        $form = $event->getForm();

        $settingsEntry = $this->settingsManager->getSettingByKey(
            SettingsFormType::ALLOW_CUSTOMERS_PROFILE_EDITS_SETTINGS_KEY
        );

        /** @var TokenInterface $token */
        $token = $this->tokenStorage->getToken();

        if (in_array('ROLE_ADMIN', $token->getRoles()) || in_array('ROLE_SELLER', $token->getRoles())) {
            return;
        }

        if (null !== $settingsEntry && !$settingsEntry->getValue()) {
            foreach ($form->all() as $name => $value) {
                if (!in_array($name, self::FORM_FIELDS_ALLOWED_TO_EDIT) && $value->isSubmitted()) {
                    $form
                        ->get($name)
                        ->addError(
                            new FormError($this->translator->trans('customer.profile_edit.field_edit_not_allowed'))
                        )
                    ;
                }
            }
        }
    }
}
