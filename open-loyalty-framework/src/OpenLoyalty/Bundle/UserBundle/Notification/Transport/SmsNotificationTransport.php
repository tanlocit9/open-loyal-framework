<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Notification\Transport;

use OpenLoyalty\Bundle\ActivationCodeBundle\Service\SmsSender;
use OpenLoyalty\Bundle\SettingsBundle\Service\GeneralSettingsManagerInterface;
use OpenLoyalty\Bundle\SmsApiBundle\Message\Message;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetails;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SmsNotificationTransport.
 */
class SmsNotificationTransport implements NotificationTransportInterface
{
    /**
     * @var GeneralSettingsManagerInterface
     */
    private $generalSettingsManager;

    /**
     * @var SmsSender
     */
    private $smsSender;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * SmsNotificationTransport constructor.
     *
     * @param GeneralSettingsManagerInterface $generalSettingsManager
     * @param SmsSender                       $smsSender
     * @param TranslatorInterface             $translator
     * @param array                           $parameters
     */
    public function __construct(
        GeneralSettingsManagerInterface $generalSettingsManager,
        SmsSender $smsSender,
        TranslatorInterface $translator,
        array $parameters
    ) {
        $this->generalSettingsManager = $generalSettingsManager;
        $this->smsSender = $smsSender;
        $this->translator = $translator;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sendInvitation(InvitationDetails $invitation): void
    {
        if ($invitation->getRecipientPhone()) {
            $refUrl = sprintf(
                '%s%s%s',
                $this->parameters['customer_url'] ?? '',
                $this->parameters['frontend_invitation_url'] ?? '',
                $invitation->getToken()
            );

            $content = sprintf($this->translator->trans('invitation.send_message'), $refUrl);

            $this->smsSender->send(Message::create(
                $invitation->getRecipientPhone(),
                $this->generalSettingsManager->getProgramName(),
                $content
            ));
        }
    }

    /**
     * @param array $notification
     */
    public function sendRewardAvailableNotification(array $notification): void
    {
        // SKIP
    }
}
