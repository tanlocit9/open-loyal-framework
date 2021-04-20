<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\ReadModel;

use Broadway\Repository\Repository as AggregateRootRepository;
use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Core\Infrastructure\Projector\Projector;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasAttachedToInvitation;
use OpenLoyalty\Component\Customer\Domain\Event\InvitationWasCreated;
use OpenLoyalty\Component\Customer\Domain\Event\PurchaseWasMadeForThisInvitation;
use OpenLoyalty\Component\Customer\Domain\InvitationId;

/**
 * Class InvitationDetailsProjector.
 */
class InvitationDetailsProjector extends Projector
{
    /**
     * @var AggregateRootRepository
     */
    private $customerRepository;

    /**
     * @var Repository
     */
    private $invitationDetailsRepository;

    /**
     * InvitationDetailsProjector constructor.
     *
     * @param AggregateRootRepository $customerRepository
     * @param Repository              $invitationDetailsRepository
     */
    public function __construct(
        AggregateRootRepository $customerRepository,
        Repository $invitationDetailsRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->invitationDetailsRepository = $invitationDetailsRepository;
    }

    /**
     * @param InvitationWasCreated $event
     */
    public function applyInvitationWasCreated(InvitationWasCreated $event): void
    {
        $invitationDetails = $this->getReadModel($event->getInvitationId());
        if (!$invitationDetails) {
            $customer = $this->customerRepository->load((string) $event->getReferrerId());
            if (!$customer instanceof Customer) {
                return;
            }

            $invitationDetails = new InvitationDetails(
                $event->getInvitationId(),
                $event->getReferrerId(),
                $customer->getEmail(),
                $customer->getFirstName().' '.$customer->getLastName(),
                $event->getRecipientEmail(),
                $event->getRecipientPhone(),
                $event->getToken()
            );
        }

        $this->invitationDetailsRepository->save($invitationDetails);
    }

    /**
     * @param CustomerWasAttachedToInvitation $event
     */
    public function applyCustomerWasAttachedToInvitation(CustomerWasAttachedToInvitation $event): void
    {
        $invitationDetails = $this->getReadModel($event->getInvitationId());
        if (!$invitationDetails) {
            return;
        }
        $name = '';
        $customer = $this->customerRepository->load((string) $event->getCustomerId());
        if ($customer instanceof Customer) {
            $name = $customer->getFirstName().' '.$customer->getLastName();
        }

        $invitationDetails->updateRecipientData($event->getCustomerId(), $name);

        $this->invitationDetailsRepository->save($invitationDetails);
    }

    /**
     * @param PurchaseWasMadeForThisInvitation $event
     */
    public function applyPurchaseWasMadeForThisInvitation(PurchaseWasMadeForThisInvitation $event): void
    {
        $invitationDetails = $this->getReadModel($event->getInvitationId());
        if (!$invitationDetails) {
            return;
        }

        $invitationDetails->madePurchase();

        $this->invitationDetailsRepository->save($invitationDetails);
    }

    /**
     * @param InvitationId $invitationId
     *
     * @return InvitationDetails|null
     */
    private function getReadModel(InvitationId $invitationId): ?InvitationDetails
    {
        /** @var InvitationDetails $readModel */
        $readModel = $this->invitationDetailsRepository->find((string) $invitationId);

        return $readModel;
    }
}
