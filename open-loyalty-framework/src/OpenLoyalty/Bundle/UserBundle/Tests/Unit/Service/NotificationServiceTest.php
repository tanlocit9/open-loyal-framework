<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Service;

use OpenLoyalty\Bundle\UserBundle\Service\NotificationService;
use OpenLoyalty\Bundle\UserBundle\Notification\Transport\NotificationTransportInterface;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\InvitationId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetails;
use PHPUnit\Framework\TestCase;

/**
 * Class NotificationServiceTest.
 */
class NotificationServiceTest extends TestCase
{
    /**
     * @test
     */
    public function it_sends_invitation_to_one_available_transports(): void
    {
        $availableTransport = $this->getMockBuilder(NotificationTransportInterface::class)->getMock();
        $availableTransport->expects($this->once())->method('isAvailable')->willReturn(true);
        $availableTransport->expects($this->once())->method('sendInvitation');

        $invitation = new InvitationDetails(
            new InvitationId('00000000-0000-474c-b092-b0dd880c07e2'),
            new CustomerId('00000000-0000-474c-b092-b0dd880c07e2'),
            'mock@example.com',
            'test',
            null,
            null,
            'xL3akDS03'
        );

        $notification = new NotificationService();
        $notification->addTransport($availableTransport);
        $notification->sendInvitation($invitation);
    }

    /**
     * @test
     */
    public function it_sends_invitation_to_all_available_transports(): void
    {
        $availableTransport1 = $this->getMockBuilder(NotificationTransportInterface::class)->getMock();
        $availableTransport1->expects($this->once())->method('isAvailable')->willReturn(true);
        $availableTransport1->expects($this->once())->method('sendInvitation');

        $availableTransport2 = $this->getMockBuilder(NotificationTransportInterface::class)->getMock();
        $availableTransport2->expects($this->once())->method('isAvailable')->willReturn(true);
        $availableTransport2->expects($this->once())->method('sendInvitation');

        $invitation = new InvitationDetails(
            new InvitationId('00000000-0000-474c-b092-b0dd880c07e2'),
            new CustomerId('00000000-0000-474c-b092-b0dd880c07e2'),
            'mock@example.com',
            'test',
            null,
            null,
            'xL3akDS03'
        );

        $notification = new NotificationService();
        $notification->addTransport($availableTransport1);
        $notification->addTransport($availableTransport2);

        $notification->sendInvitation($invitation);
    }
}
