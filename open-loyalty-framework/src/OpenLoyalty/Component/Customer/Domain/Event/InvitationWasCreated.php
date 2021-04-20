<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Event;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Invitation;
use OpenLoyalty\Component\Customer\Domain\InvitationId;

/**
 * Class InvitationWasCreated.
 */
class InvitationWasCreated extends InvitationEvent
{
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
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $token;

    public function __construct(InvitationId $invitationId, CustomerId $referrerId, ?string $recipientEmail, ?string $recipientPhone, string $token)
    {
        parent::__construct($invitationId);
        $this->recipientEmail = $recipientEmail;
        $this->recipientPhone = $recipientPhone;
        $this->referrerId = $referrerId;
        $this->status = Invitation::STATUS_INVITED;
        $this->token = $token;
    }

    /**
     * @return CustomerId
     */
    public function getReferrerId()
    {
        return $this->referrerId;
    }

    /**
     * @return string|null
     */
    public function getRecipientEmail(): ?string
    {
        return $this->recipientEmail;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return null|string
     */
    public function getRecipientPhone(): ?string
    {
        return $this->recipientPhone;
    }

    public function serialize(): array
    {
        return array_merge(
            parent::serialize(),
            [
                'recipientEmail' => $this->recipientEmail,
                'recipientPhone' => $this->recipientPhone,
                'referrerId' => (string) $this->referrerId,
                'status' => $this->status,
                'token' => $this->token,
            ]
        );
    }

    /**
     * @param array $data
     *
     * @return InvitationWasCreated
     */
    public static function deserialize(array $data)
    {
        $invitation = new self(
            new InvitationId($data['invitationId']),
            new CustomerId($data['referrerId']),
            $data['recipientEmail'] ?? null,
            $data['recipientPhone'] ?? null,
            $data['token']
        );
        $invitation->status = $data['status'];

        return $invitation;
    }
}
