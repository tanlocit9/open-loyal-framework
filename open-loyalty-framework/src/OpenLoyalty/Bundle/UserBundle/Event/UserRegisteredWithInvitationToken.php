<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Event;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class UserRegisteredWithInvitationToken.
 */
class UserRegisteredWithInvitationToken extends Event
{
    const NAME = 'user.invitation.user_registered_with_invitation_token';

    /**
     * @var string
     */
    private $invitationToken;

    /**
     * @var CustomerId
     */
    private $customerId;

    /**
     * UserRegisteredWithInvitationToken constructor.
     *
     * @param string     $invitationToken
     * @param CustomerId $customerId
     */
    public function __construct(string $invitationToken, CustomerId $customerId)
    {
        $this->invitationToken = $invitationToken;
        $this->customerId = $customerId;
    }

    /**
     * @return string
     */
    public function getInvitationToken(): string
    {
        return $this->invitationToken;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }
}
