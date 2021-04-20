<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Customer\Domain\Command\AttachCustomerToInvitation;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Event\CustomerWasAttachedToInvitation;
use OpenLoyalty\Component\Customer\Domain\Event\InvitationWasCreated;
use OpenLoyalty\Component\Customer\Domain\InvitationId;

/**
 * Class AttachCustomerToInvitationTest.
 */
final class AttachCustomerToInvitationTest extends InvitationCommandHandlerTest
{
    /**
     * @test
     */
    public function it_creates_new_email_invitation(): void
    {
        $invitationId = new InvitationId('00000000-0000-0000-0000-000000000000');
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000001');

        $this->scenario
            ->withAggregateId((string) $invitationId)
            ->given([
                new InvitationWasCreated($invitationId, $customerId, 'test@oloy.com', null, '123'),
            ])
            ->when(new AttachCustomerToInvitation($invitationId, $customerId))
            ->then(array(
                new CustomerWasAttachedToInvitation($invitationId, $customerId),
            ));
    }

    /**
     * @test
     */
    public function it_creates_new_mobile_invitation(): void
    {
        $invitationId = new InvitationId('00000000-0000-0000-0000-000000000000');
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000001');

        $this->scenario
            ->withAggregateId((string) $invitationId)
            ->given([
                new InvitationWasCreated($invitationId, $customerId, null, '123123123', '123'),
            ])
            ->when(new AttachCustomerToInvitation($invitationId, $customerId))
            ->then(array(
                new CustomerWasAttachedToInvitation($invitationId, $customerId),
            ));
    }
}
