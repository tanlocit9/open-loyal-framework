<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Customer\Domain\Command\CreateInvitation;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Event\InvitationWasCreated;
use OpenLoyalty\Component\Customer\Domain\Invitation;
use OpenLoyalty\Component\Customer\Domain\InvitationId;

/**
 * Class CreateInvitationTest.
 */
final class CreateInvitationTest extends InvitationCommandHandlerTest
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
            ->given([])
            ->when(new CreateInvitation($invitationId, $customerId, Invitation::EMAIL_TYPE, 'test@oloy.com'))
            ->then(array(
                new InvitationWasCreated($invitationId, $customerId, 'test@oloy.com', null, '123'),
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
            ->given([])
            ->when(new CreateInvitation($invitationId, $customerId, Invitation::MOBILE_TYPE, '123123123'))
            ->then(array(
                new InvitationWasCreated($invitationId, $customerId, null, '123123123', '123'),
            ));
    }
}
