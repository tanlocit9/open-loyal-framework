<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Account\Tests\Unit\Infrastructure\Notifier;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetails;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetailsRepository;
use OpenLoyalty\Component\Account\Infrastructure\Notifier\ExpirePointsNotifier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class ExpirePointsNotifierTest.
 */
final class ExpirePointsNotifierTest extends TestCase
{
    /**
     * @var CommandBus|MockObject
     */
    private $commandBusMock;

    /**
     * @var PointsTransferDetailsRepository|MockObject
     */
    private $pointTransfersRepositoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->commandBusMock = $this->getMockForAbstractClass(CommandBus::class);
        $this->pointTransfersRepositoryMock = $this->getMockForAbstractClass(PointsTransferDetailsRepository::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
    }

    /**
     * @test
     */
    public function it_dispatches_webhook_if_there_are_expiring_point_transfers(): void
    {
        /** @var PointsTransferDetails|MockObject $pointTransferMock */
        $pointTransferMock = $this
            ->getMockBuilder(PointsTransferDetails::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $pointTransferMock->method('getCustomerId')->willReturn('22200000-0000-474c-b092-b0dd880c07e2');
        $pointTransferMock->method('getCustomerEmail')->willReturn('test@doe.com');
        $pointTransferMock->method('getCustomerPhone')->willReturn('111222333');
        $pointTransferMock->method('getCustomerLoyaltyCardNumber')->willReturn('test');
        $pointTransferMock->method('getCustomerFirstName')->willReturn('John');
        $pointTransferMock->method('getCustomerLastName')->willReturn('Doe');
        $pointTransferMock->method('getValue')->willReturn(10);
        $pointTransferMock->method('getExpiresAt')->willReturn(new \DateTime());

        $this->pointTransfersRepositoryMock
            ->method('findAllActiveAddingTransfersExpiredAt')
            ->willReturn([$pointTransferMock])
        ;

        $expirePointsNotifier = new ExpirePointsNotifier(
            $this->commandBusMock,
            $this->pointTransfersRepositoryMock,
            $this->loggerMock
        );
        $expirePointsNotifier->sendNotificationsForPointsExpiringAt(new \DateTime('now'));

        $this->assertEquals(1, $expirePointsNotifier->sentNotificationsCount());
    }

    /**
     * @test
     */
    public function it_does_not_dispatch_webhook_when_there_are_no_expiring_point_transfers(): void
    {
        $this->pointTransfersRepositoryMock
            ->method('findAllActiveAddingTransfersExpiredAt')
            ->willReturn([])
        ;

        $expirePointsNotifier = new ExpirePointsNotifier(
            $this->commandBusMock,
            $this->pointTransfersRepositoryMock,
            $this->loggerMock
        );
        $expirePointsNotifier->sendNotificationsForPointsExpiringAt(new \DateTime('now'));

        $this->assertEquals(0, $expirePointsNotifier->sentNotificationsCount());
    }
}
