<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Account\Tests\Unit\Domain;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use OpenLoyalty\Component\Account\Domain\Account;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\Account\Domain\Event\AccountWasCreated;
use OpenLoyalty\Component\Account\Domain\Event\PointsHasBeenReset;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenCanceled;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenExpired;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenUnlocked;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereAdded;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereSpent;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereTransferred;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\P2PSpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\PointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\SpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AccountTest.
 */
final class AccountTest extends AggregateRootScenarioTestCase
{
    protected $accountId = '00000000-0000-0000-0000-000000000000';
    protected $customerId = '00000000-0000-0000-0000-000000000000';

    /**
     * {@inheritdoc}
     */
    protected function getAggregateRootClass(): string
    {
        return Account::class;
    }

    /**
     * @test
     */
    public function it_can_have_an_account(): void
    {
        $accountId = new AccountId('00000000-0000-0000-0000-000000000000');
        $customerId = new CustomerId('00000000-0000-0000-0000-000000000001');

        $this->scenario
            ->when(function () use ($accountId, $customerId) {
                return Account::createAccount($accountId, $customerId);
            })
            ->then([
                new AccountWasCreated(
                    $accountId,
                    $customerId
                ),
            ]);
    }

    /**
     * @test
     */
    public function it_can_add_points(): void
    {
        $id = '00000000-0000-0000-0000-000000000000';

        $addPointsTransfer = new AddPointsTransfer(
            new PointsTransferId('00000000-0000-0000-0000-000000000004'),
            1
        );

        $this->scenario
            ->withAggregateId($id)
            ->given([new AccountWasCreated(
                new AccountId('00000000-0000-0000-0000-000000000001'),
                new CustomerId('00000000-0000-0000-0000-000000000002')
             )])
            ->when(function (Account $account) use ($addPointsTransfer) {
                $account->addPoints($addPointsTransfer);
            })
            ->then([
                new PointsWereAdded(
                    new AccountId('00000000-0000-0000-0000-000000000001'),
                    $addPointsTransfer
                ),
            ]);
    }

    /**
     * @test
     *
     * @expectedException \OpenLoyalty\Component\Account\Domain\Exception\NotEnoughPointsException
     */
    public function it_cannot_spend_points_when_not_enough_points(): void
    {
        $id = '00000000-0000-0000-0000-000000000000';

        $spendPointsTransfer = new SpendPointsTransfer(
            new PointsTransferId('00000000-0000-0000-0000-000000000005'),
            1
        );

        $this->scenario
            ->withAggregateId($id)
            ->given([new AccountWasCreated(
                         new AccountId('00000000-0000-0000-0000-000000000001'),
                         new CustomerId('00000000-0000-0000-0000-000000000002')
                     )])
            ->when(function (Account $account) use ($spendPointsTransfer) {
                $account->spendPoints($spendPointsTransfer);
            });
    }

    /**
     * @test
     */
    public function it_can_spend_points(): void
    {
        $id = '00000000-0000-0000-0000-000000000000';

        $spendPointsTransfer = new SpendPointsTransfer(
            new PointsTransferId('00000000-0000-0000-0000-000000000005'),
            1
        );

        $addPointsTransfer = new AddPointsTransfer(
            new PointsTransferId('00000000-0000-0000-0000-000000000004'),
            1
        );

        $this->scenario
            ->withAggregateId($id)
            ->given([new AccountWasCreated(
                         new AccountId('00000000-0000-0000-0000-000000000001'),
                         new CustomerId('00000000-0000-0000-0000-000000000002')
                     )])
            ->when(function (Account $account) use ($addPointsTransfer) {
                $account->addPoints($addPointsTransfer);
            })
            ->when(function (Account $account) use ($spendPointsTransfer) {
                $account->spendPoints($spendPointsTransfer);
            })
            ->then([
                new PointsWereAdded(
                    new AccountId('00000000-0000-0000-0000-000000000001'),
                    $addPointsTransfer
                ),
                new PointsWereSpent(
                    new AccountId('00000000-0000-0000-0000-000000000001'),
                    $spendPointsTransfer
                ),
            ]);
    }

    /**
     * @test
     */
    public function it_can_cancel_points_transfer(): void
    {
        $id = '00000000-0000-0000-0000-000000000000';

        $pointsTransferId = new PointsTransferId('00000000-0000-0000-0000-000000000004');

        $addPointsTransfer = new AddPointsTransfer($pointsTransferId, 1);

        $this->scenario
            ->withAggregateId($id)
            ->given([new AccountWasCreated(
                         new AccountId('00000000-0000-0000-0000-000000000001'),
                         new CustomerId('00000000-0000-0000-0000-000000000002')
                     )])
            ->when(function (Account $account) use ($addPointsTransfer) {
                $account->addPoints($addPointsTransfer);
            })
            ->when(function (Account $account) use ($pointsTransferId) {
                $account->cancelPointsTransfer($pointsTransferId);
            })
            ->then([
                new PointsWereAdded(
                    new AccountId('00000000-0000-0000-0000-000000000001'),
                    $addPointsTransfer
                ),
                new PointsTransferHasBeenCanceled(
                    new AccountId('00000000-0000-0000-0000-000000000001'),
                    $pointsTransferId
                ),
            ]);
    }

    /**
     * @test
     */
    public function it_can_expire_points_transfer(): void
    {
        $id = '00000000-0000-0000-0000-000000000000';

        $pointsTransferId = new PointsTransferId('00000000-0000-0000-0000-000000000004');

        $addPointsTransfer = new AddPointsTransfer($pointsTransferId, 1);

        $this->scenario
            ->withAggregateId($id)
            ->given([new AccountWasCreated(
                         new AccountId('00000000-0000-0000-0000-000000000001'),
                         new CustomerId('00000000-0000-0000-0000-000000000002')
                     )])
            ->when(function (Account $account) use ($addPointsTransfer) {
                $account->addPoints($addPointsTransfer);
            })
            ->when(function (Account $account) use ($pointsTransferId) {
                $account->expirePointsTransfer($pointsTransferId);
            })
            ->then([
                new PointsWereAdded(
                    new AccountId('00000000-0000-0000-0000-000000000001'),
                    $addPointsTransfer
                ),
                new PointsTransferHasBeenExpired(
                    new AccountId('00000000-0000-0000-0000-000000000001'),
                    $pointsTransferId
                ),
            ]);
    }

    /**
     * @test
     */
    public function it_can_reset_points(): void
    {
        $id = '00000000-0000-0000-0000-000000000000';

        $pointsTransferId = new PointsTransferId('00000000-0000-0000-0000-000000000004');

        $addPointsTransfer = new AddPointsTransfer($pointsTransferId, 1);

        $expireDate = new \DateTime();

        $this->scenario
            ->withAggregateId($id)
            ->given([new AccountWasCreated(
                         new AccountId('00000000-0000-0000-0000-000000000001'),
                         new CustomerId('00000000-0000-0000-0000-000000000002')
                     )])
            ->when(function (Account $account) use ($addPointsTransfer) {
                $account->addPoints($addPointsTransfer);
            })
            ->when(function (Account $account) use ($expireDate) {
                $account->resetPoints($expireDate);
            })
            ->then([
                new PointsWereAdded(
                    new AccountId('00000000-0000-0000-0000-000000000001'),
                    $addPointsTransfer
                ),
                new PointsHasBeenReset(
                    new AccountId('00000000-0000-0000-0000-000000000001'),
                    $expireDate
                ),
            ]);
    }

    /**
     * @test
     */
    public function it_can_unlock_points_transfer(): void
    {
        $id = '00000000-0000-0000-0000-000000000000';

        $pointsTransferId = new PointsTransferId('00000000-0000-0000-0000-000000000004');

        $addPointsTransfer = new AddPointsTransfer($pointsTransferId, 1);

        $this->scenario
            ->withAggregateId($id)
            ->given([new AccountWasCreated(
                         new AccountId('00000000-0000-0000-0000-000000000001'),
                         new CustomerId('00000000-0000-0000-0000-000000000002')
                     )])
            ->when(function (Account $account) use ($addPointsTransfer) {
                $account->addPoints($addPointsTransfer);
            })
            ->when(function (Account $account) use ($pointsTransferId) {
                $account->unlockPointsTransfer($pointsTransferId);
            })
            ->then([
                new PointsWereAdded(
                    new AccountId('00000000-0000-0000-0000-000000000001'),
                    $addPointsTransfer
                ),
                new PointsTransferHasBeenUnlocked(
                    new AccountId('00000000-0000-0000-0000-000000000001'),
                    $pointsTransferId
                ),
            ]);
    }

    /**
     * @test
     */
    public function it_can_transfer_points(): void
    {
        $id = '00000000-0000-0000-0000-000000000000';

        $pointsTransferId = new PointsTransferId('00000000-0000-0000-0000-000000000004');

        $addPointsTransfer = new AddPointsTransfer($pointsTransferId, 1);

        $p2pSpendPointsTransfer = new P2PSpendPointsTransfer(
            new AccountId('00000000-0000-0000-0000-000000000002'),
            new PointsTransferId('00000000-0000-0000-0000-000000000005'),
            1
        );

        $this->scenario
            ->withAggregateId($id)
            ->given([new AccountWasCreated(
                         new AccountId('00000000-0000-0000-0000-000000000001'),
                         new CustomerId('00000000-0000-0000-0000-000000000002')
                     )])
            ->when(function (Account $account) use ($addPointsTransfer) {
                $account->addPoints($addPointsTransfer);
            })
            ->when(function (Account $account) use ($p2pSpendPointsTransfer) {
                $account->transferPoints($p2pSpendPointsTransfer);
            })
            ->then([
                new PointsWereAdded(
                    new AccountId('00000000-0000-0000-0000-000000000001'),
                    $addPointsTransfer
                ),
                new PointsWereTransferred(
                    new AccountId('00000000-0000-0000-0000-000000000001'),
                    $p2pSpendPointsTransfer
                ),
            ]);
    }

    /**
     * @test
     */
    public function it_returns_points_transfer_by_id(): void
    {
        /** @var Account|MockObject $account */
        $account = $this->getMockBuilder(Account::class)
                        ->setMethodsExcept(['setPointsTransfers', 'getTransferById'])
                        ->getMock();

        $account->setPointsTransfers([
            '00000000-0000-0000-0000-000000000001' => $this->getPointsTransferMock(),
            '00000000-0000-0000-0000-000000000002' => $this->getPointsTransferMock(),
        ]);

        $this->assertInstanceOf(PointsTransfer::class, $account->getTransferById(
            new PointsTransferId('00000000-0000-0000-0000-000000000001')
        ));
    }

    /**
     * @test
     */
    public function it_returns_null_when_get_points_transfer_by_id_not_exists(): void
    {
        /** @var Account|MockObject $account */
        $account = $this->getMockBuilder(Account::class)
                        ->setMethodsExcept(['setPointsTransfers', 'getTransferById'])
                        ->getMock();

        $account->setPointsTransfers([
            '00000000-0000-0000-0000-000000000001' => $this->getPointsTransferMock(),
            '00000000-0000-0000-0000-000000000002' => $this->getPointsTransferMock(),
        ]);

        $this->assertNull($account->getTransferById(
            new PointsTransferId('00000000-0000-0000-0000-000000000003')
        ));
    }

    /**
     * @return MockObject|PointsTransfer
     */
    protected function getPointsTransferMock(): MockObject
    {
        return $this->getMockBuilder(PointsTransfer::class)->disableOriginalConstructor()->getMock();
    }
}
