<?php

namespace OpenLoyalty\Component\Customer\Domain\Event;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\InvitationId;

/**
 * Class CustomerWasAttachedToInvitation.
 */
class CustomerWasAttachedToInvitation extends InvitationEvent
{
    /**
     * @var CustomerId
     */
    private $customerId;

    public function __construct(InvitationId $invitationId, CustomerId $customerId)
    {
        $this->customerId = $customerId;
        parent::__construct($invitationId);
    }

    public function serialize(): array
    {
        return array_merge(parent::serialize(), [
           'customerId' => (string) $this->customerId,
        ]);
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new self(new InvitationId($data['invitationId']), new CustomerId($data['customerId']));
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }
}
