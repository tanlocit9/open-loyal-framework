<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\EventListener;

use Broadway\CommandHandling\CommandBus;
use Broadway\Repository\Repository;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Bundle\SettingsBundle\Service\GeneralSettingsManagerInterface;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Event\UserRegisteredWithInvitationToken;
use OpenLoyalty\Bundle\UserBundle\Service\CustomerProvider;
use OpenLoyalty\Component\Customer\Domain\Command\AttachCustomerToInvitation;
use OpenLoyalty\Component\Customer\Domain\Command\CreateInvitation;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Invitation;
use OpenLoyalty\Component\Customer\Domain\InvitationId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetailsRepository;

/**
 * Class UserRegisteredWithInvitationTokenListener.
 */
class UserRegisteredWithInvitationTokenListener
{
    /**
     * @var InvitationDetailsRepository
     */
    private $invitationDetailsRepository;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var GeneralSettingsManagerInterface
     */
    private $settingsManager;

    /**
     * @var CustomerProvider
     */
    private $customerProvider;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * @var Repository
     */
    private $customerRepository;

    /**
     * @var Repository
     */
    private $invitationRepository;

    /**
     * UserRegisteredWithInvitationTokenListener constructor.
     *
     * @param InvitationDetailsRepository     $invitationDetailsRepository
     * @param CommandBus                      $commandBus
     * @param GeneralSettingsManagerInterface $settingsManager
     * @param CustomerProvider                $customerProvider
     * @param UuidGeneratorInterface          $uuidGenerator
     * @param Repository                      $customerRepository
     * @param Repository                      $invitationRepository
     */
    public function __construct(
        InvitationDetailsRepository $invitationDetailsRepository,
        CommandBus $commandBus,
        GeneralSettingsManagerInterface $settingsManager,
        CustomerProvider $customerProvider,
        UuidGeneratorInterface $uuidGenerator,
        Repository $customerRepository,
        Repository $invitationRepository
    ) {
        $this->invitationDetailsRepository = $invitationDetailsRepository;
        $this->commandBus = $commandBus;
        $this->settingsManager = $settingsManager;
        $this->customerProvider = $customerProvider;
        $this->uuidGenerator = $uuidGenerator;
        $this->customerRepository = $customerRepository;
        $this->invitationRepository = $invitationRepository;
    }

    /**
     * @param UserRegisteredWithInvitationToken $event
     */
    public function handle(UserRegisteredWithInvitationToken $event): void
    {
        $invitationToken = $event->getInvitationToken();

        // if account activation method is SMS, use customer's phone number as the token and create
        // an invitation token entity for the business process
        if ($this->settingsManager->isSmsAccountActivationMethod()) {
            try {
                /** @var Customer $customer */
                $customer = $this->customerProvider->loadUserByPhoneNumber($event->getInvitationToken());
                /** @var \OpenLoyalty\Component\Customer\Domain\Customer $referral */
                $referral = $this->customerRepository->load($event->getCustomerId());
                $invitationId = new InvitationId($this->uuidGenerator->generate());
                $this->commandBus->dispatch(new CreateInvitation(
                    $invitationId,
                    new CustomerId($customer->getId()),
                    Invitation::MOBILE_TYPE,
                    $referral->getPhone()
                ));

                /** @var Invitation $invitation */
                $invitation = $this->invitationRepository->load((string) $invitationId);
                $invitationToken = $invitation->getToken();
            } catch (\Exception $e) {
                return;
            }
        }

        $invitations = $this->invitationDetailsRepository->findByToken($invitationToken);
        if (count($invitations) > 0) {
            $invitation = reset($invitations);
            if ($invitation instanceof InvitationDetails && !$invitation->getRecipientId()) {
                $this->commandBus
                    ->dispatch(new AttachCustomerToInvitation($invitation->getInvitationId(), $event->getCustomerId()));
            }
        }
    }
}
