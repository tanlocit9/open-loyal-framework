<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Account\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Command\UnlockPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Event\AccountWasCreated;
use OpenLoyalty\Component\Account\Domain\Event\PointsTransferHasBeenUnlocked;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereAdded;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\CustomerId;

/**
 * Class UnlockPointsTransferTest.
 */
final class UnlockPointsTransferTest extends AccountCommandHandlerTest
{
    /**
     * @test
     */
    public function it_unlock_points()
    {
        $accountId = new AccountId('00000000-0000-0000-0000-000000000000');
        $customerId = new CustomerId('00000000-1111-0000-0000-000000000000');
        $pointsTransferId = new PointsTransferId('00000000-1111-0000-0000-000000000111');
        $this->scenario
            ->withAggregateId((string) $accountId)
            ->given([
                new AccountWasCreated($accountId, $customerId),
                new PointsWereAdded($accountId, new AddPointsTransfer($pointsTransferId, 100, 5, 10, new \DateTime('-11 days'))),
            ])
            ->when(new UnlockPointsTransfer($accountId, $pointsTransferId))
            ->then(array(
                new PointsTransferHasBeenUnlocked($accountId, $pointsTransferId),
            ));
    }
}
