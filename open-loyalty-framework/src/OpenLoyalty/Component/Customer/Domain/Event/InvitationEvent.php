<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Event;

use Broadway\Serializer\Serializable;
use OpenLoyalty\Component\Customer\Domain\InvitationId;

/**
 * Class InvitationEvent.
 */
abstract class InvitationEvent implements Serializable
{
    /**
     * @var InvitationId
     */
    private $invitationId;

    /**
     * InvitationEvent constructor.
     *
     * @param InvitationId $invitationId
     */
    public function __construct(InvitationId $invitationId)
    {
        $this->invitationId = $invitationId;
    }

    /**
     * @return InvitationId
     */
    public function getInvitationId()
    {
        return $this->invitationId;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return ['invitationId' => (string) $this->invitationId];
    }
}
