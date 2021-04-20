<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Account\Tests\Unit\Domain\ReadModel;

use Broadway\ReadModel\Projector;
use Broadway\ReadModel\InMemory\InMemoryRepository;
use Broadway\ReadModel\Testing\ProjectorScenarioTestCase;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Event\AccountWasCreated;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenUnlocked;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereAdded;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereTransferred;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\P2PSpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\Event\PointsHasBeenReset;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetailsProjector;
use OpenLoyalty\Component\Account\Domain\CustomerId;

/**
 * Class AccountDetailsProjectorTest.
 */
final class AccountDetailsProjectorTest extends ProjectorScenarioTestCase
{
    /**
     * @var AccountId
     */
    protected $accountId;

    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var PointsTransferId
     */
    protected $pointsTransferId;

    /**
     * {@inheritdoc}
     */
    protected function createProjector(InMemoryRepository $repository): Projector
    {
        $this->accountId = new AccountId('00000000-0000-0000-0000-000000000000');
        $this->customerId = new CustomerId('00000000-1111-0000-0000-000000000000');
        $this->pointsTransferId = new PointsTransferId('00000000-1111-0000-0000-000000000000');

        return new AccountDetailsProjector($repository);
    }

    /**
     * @test
     */
    public function it_creates_a_read_model(): void
    {
        $this->scenario->given(array())
            ->when(new AccountWasCreated($this->accountId, $this->customerId))
            ->then(array(
                $this->createReadModel(),
            ));
    }

    /**
     * @test
     */
    public function it_unlocks_points(): void
    {
        $pointsId = new PointsTransferId('00000000-0000-0000-0000-000000000000');
        $date = new \DateTime();
        $expectedReadModel = $this->createReadModel();
        $pointsTransfer = new AddPointsTransfer($pointsId, 100, 10, 10, $date);
        $pointsTransfer->unlock();
        $expectedReadModel->addPointsTransfer($pointsTransfer);
        $this->scenario->given(array())
            ->given([
                new AccountWasCreated($this->accountId, $this->customerId),
                new PointsWereAdded($this->accountId, new AddPointsTransfer($pointsId, 100, 10, 10, $date)),
            ])
            ->when(new PointsTransferHasBeenUnlocked($this->accountId, $pointsId))
            ->then(array(
                $expectedReadModel,
            ));
    }

    /**
     * @test
     */
    public function it_expires_all_active_and_locked_points_on_reset(): void
    {
        $date = new \DateTime();
        $expectedReadModel = $this->createReadModel();
        $pointsTransfer = new AddPointsTransfer($this->pointsTransferId, 100, 40);
        $pointsTransfer->expire();
        $expectedReadModel->setPointsResetAt($date);
        $expectedReadModel->addPointsTransfer($pointsTransfer);

        $this->scenario
            ->given([
                new AccountWasCreated($this->accountId, $this->customerId),
                new PointsWereAdded($this->accountId, new AddPointsTransfer($this->pointsTransferId, 100, 40)),
            ])
            ->when(new PointsHasBeenReset($this->accountId, $date))
            ->then([
                $expectedReadModel,
            ]);
    }

    /**
     * @test
     */
    public function it_transfer_points(): void
    {
        $pointsId = new PointsTransferId('00000000-0000-0000-0000-000000000000');
        $points2Id = new PointsTransferId('00000000-0000-0000-0000-000000000001');
        $date = new \DateTime();
        $expectedReadModel = $this->createReadModel();
        $pointsTransfer = new AddPointsTransfer($pointsId, 100, null, null, $date);
        $pointsTransfer->updateAvailableAmount(10);
        $transfer = new P2PSpendPointsTransfer($this->accountId, $points2Id, 90);

        $expectedReadModel->addPointsTransfer($pointsTransfer);
        $expectedReadModel->addPointsTransfer($transfer);

        $this->scenario->given(array())
            ->given([
                new AccountWasCreated($this->accountId, $this->customerId),
                new PointsWereAdded($this->accountId, new AddPointsTransfer($pointsId, 100, null, null, $date)),
            ])
            ->when(new PointsWereTransferred($this->accountId, $transfer))
            ->then(array(
                $expectedReadModel,
            ));
    }

    /**
     * @return AccountDetails
     */
    private function createReadModel(): AccountDetails
    {
        return new AccountDetails($this->accountId, $this->customerId);
    }
}
