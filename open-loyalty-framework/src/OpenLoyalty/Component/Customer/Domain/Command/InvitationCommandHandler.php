<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\EventDispatcher\EventDispatcher;
use OpenLoyalty\Component\Customer\Domain\Invitation;
use OpenLoyalty\Component\Customer\Domain\InvitationRepository;
use OpenLoyalty\Component\Customer\Domain\Service\InvitationTokenGenerator;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerAttachedToInvitationSystemEvent;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\InvitationSystemEvents;

/**
 * Class InvitationCommandHandler.
 */
class InvitationCommandHandler extends SimpleCommandHandler
{
    /**
     * @var InvitationRepository
     */
    private $invitationRepository;

    /**
     * @var InvitationTokenGenerator
     */
    private $tokenGenerator;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * InvitationCommandHandler constructor.
     *
     * @param InvitationRepository     $invitationRepository
     * @param InvitationTokenGenerator $tokenGenerator
     * @param EventDispatcher          $eventDispatcher
     */
    public function __construct(
        InvitationRepository $invitationRepository,
        InvitationTokenGenerator $tokenGenerator,
        EventDispatcher $eventDispatcher
    ) {
        $this->invitationRepository = $invitationRepository;
        $this->tokenGenerator = $tokenGenerator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleCreateInvitation(CreateInvitation $command)
    {
        $token = $this->tokenGenerator->generate($command->getReferrerId(), $command->getRecipient());
        $invitation = Invitation::createInvitation(
            $command->getInvitationId(),
            $command->getReferrerId(),
            $command->getType() === Invitation::EMAIL_TYPE ? $command->getRecipient() : null,
            $command->getType() === Invitation::MOBILE_TYPE ? $command->getRecipient() : null,
            $token
        );

        $this->invitationRepository->save($invitation);
    }

    public function handleAttachCustomerToInvitation(AttachCustomerToInvitation $command)
    {
        /** @var Invitation $invitation */
        $invitation = $this->invitationRepository->load($command->getInvitationId());
        $invitation->attachCustomer($command->getCustomerId());
        $this->invitationRepository->save($invitation);

        $this->eventDispatcher->dispatch(
            InvitationSystemEvents::CUSTOMER_ATTACHED_TO_INVITATION,
            [new CustomerAttachedToInvitationSystemEvent($command->getCustomerId(), $command->getInvitationId())]
        );
    }

    public function handleInvitedCustomerMadePurchase(InvitedCustomerMadePurchase $command)
    {
        /** @var Invitation $invitation */
        $invitation = $this->invitationRepository->load($command->getInvitationId());
        $invitation->purchaseMade();
        $this->invitationRepository->save($invitation);
    }
}
