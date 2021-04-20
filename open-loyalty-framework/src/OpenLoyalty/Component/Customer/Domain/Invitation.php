<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasAttachedToInvitation;
use OpenLoyalty\Component\Customer\Domain\Event\InvitationWasCreated;
use OpenLoyalty\Component\Customer\Domain\Event\PurchaseWasMadeForThisInvitation;

/**
 * Class Invitation.
 */
class Invitation extends EventSourcedAggregateRoot
{
    const EMAIL_TYPE = 'email';
    const MOBILE_TYPE = 'mobile';

    const STATUS_INVITED = 'invited';
    const STATUS_REGISTERED = 'registered';
    const STATUS_MADE_PURCHASE = 'made_purchase';

    /**
     * @var InvitationId
     */
    private $id;

    /**
     * @var CustomerId
     */
    private $referrerId;

    /**
     * @var string|null
     */
    private $recipientEmail;

    /**
     * @var string|null
     */
    private $recipientPhone;

    /**
     * @var CustomerId
     */
    private $recipientId;

    /**
     * @var string
     */
    private $status = self::STATUS_INVITED;

    /**
     * @var string
     */
    private $token;

    /**
     * @return string
     */
    public function getAggregateRootId(): string
    {
        return $this->id;
    }

    /**
     * @param InvitationId $invitationId
     * @param CustomerId   $referrerId
     * @param null|string  $recipientEmail
     * @param null|string  $recipientPhone
     * @param string       $token
     *
     * @return Invitation
     */
    public static function createInvitation(InvitationId $invitationId, CustomerId $referrerId, ?string $recipientEmail, ?string $recipientPhone, string $token): Invitation
    {
        $invitation = new self();
        $invitation->create($invitationId, $referrerId, $recipientEmail, $recipientPhone, $token);

        return $invitation;
    }

    /**
     * @param CustomerId $customerId
     */
    public function attachCustomer(CustomerId $customerId): void
    {
        $this->apply(
            new CustomerWasAttachedToInvitation($this->id, $customerId)
        );
    }

    /**
     * Made purchase.
     */
    public function purchaseMade(): void
    {
        $this->apply(
            new PurchaseWasMadeForThisInvitation($this->id)
        );
    }

    /**
     * @param PurchaseWasMadeForThisInvitation $event
     */
    protected function applyPurchaseWasMadeForThisInvitation(PurchaseWasMadeForThisInvitation $event): void
    {
        $this->status = Invitation::STATUS_MADE_PURCHASE;
    }

    /**
     * @param InvitationId $invitationId
     * @param CustomerId   $referrerId
     * @param null|string  $recipientEmail
     * @param null|string  $recipientPhone
     * @param string       $token
     */
    private function create(InvitationId $invitationId, CustomerId $referrerId, ?string $recipientEmail, ?string $recipientPhone, string $token): void
    {
        $this->apply(
            new InvitationWasCreated($invitationId, $referrerId, $recipientEmail, $recipientPhone, $token)
        );
    }

    /**
     * @param InvitationWasCreated $event
     */
    protected function applyInvitationWasCreated(InvitationWasCreated $event): void
    {
        $this->setId($event->getInvitationId());
        $this->setRecipientEmail($event->getRecipientEmail());
        $this->setRecipientPhone($event->getRecipientPhone());
        $this->setReferrerId($event->getReferrerId());
        $this->setToken($event->getToken());
    }

    /**
     * @param CustomerWasAttachedToInvitation $event
     */
    protected function applyCustomerWasAttachedToInvitation(CustomerWasAttachedToInvitation $event): void
    {
        $this->recipientId = $event->getCustomerId();
    }

    /**
     * @param InvitationId $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return CustomerId|null
     */
    public function getReferrerId(): ?CustomerId
    {
        return $this->referrerId;
    }

    /**
     * @param CustomerId $referrerId
     */
    private function setReferrerId(CustomerId $referrerId): void
    {
        $this->referrerId = $referrerId;
    }

    /**
     * @return string|null
     */
    public function getRecipientEmail(): ?string
    {
        return $this->recipientEmail;
    }

    /**
     * @param null|string $recipientEmail
     */
    private function setRecipientEmail(?string $recipientEmail): void
    {
        $this->recipientEmail = $recipientEmail;
    }

    /**
     * @param string $status
     */
    private function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return null|string
     */
    public function getRecipientPhone(): ?string
    {
        return $this->recipientPhone;
    }

    /**
     * @param null|string $recipientPhone
     */
    private function setRecipientPhone(?string $recipientPhone): void
    {
        $this->recipientPhone = $recipientPhone;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    private function setToken(string $token): void
    {
        $this->token = $token;
    }
}
