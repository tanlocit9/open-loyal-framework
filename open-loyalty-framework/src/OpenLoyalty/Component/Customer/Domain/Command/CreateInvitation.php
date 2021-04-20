<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Command;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\InvitationId;

/**
 * Class CreateInvitation.
 */
class CreateInvitation extends InvitationCommand
{
    /**
     * @var CustomerId
     */
    private $referrerId;

    /**
     * @var string
     */
    private $recipient;

    /**
     * @var string
     */
    private $type;

    /**
     * CreateInvitation constructor.
     *
     * @param InvitationId $invitationId
     * @param CustomerId   $referrerId
     * @param string       $type
     * @param string       $recipient
     */
    public function __construct(InvitationId $invitationId, CustomerId $referrerId, string $type, string $recipient)
    {
        parent::__construct($invitationId);
        $this->referrerId = $referrerId;
        $this->type = $type;
        $this->recipient = $recipient;
    }

    /**
     * @return CustomerId
     */
    public function getReferrerId(): CustomerId
    {
        return $this->referrerId;
    }

    /**
     * @return string
     */
    public function getRecipient(): string
    {
        return $this->recipient;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
