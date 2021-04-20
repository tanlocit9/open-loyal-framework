<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Account\Tests\Unit\Infrastructure\SytemEvent\Listener;

use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Command\AddPoints;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Transaction\Domain\CustomerId;
use OpenLoyalty\Component\Transaction\Domain\SystemEvent\CustomerAssignedToTransactionSystemEvent;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;
use OpenLoyalty\Component\Account\Infrastructure\SystemEvent\Listener\ApplyEarningRuleToTransactionListener;

/**
 * Class ApplyEarningRuleToTransactionListenerTest.
 */
final class ApplyEarningRuleToTransactionListenerTest extends BaseApplyEarningRuleListenerTest
{
    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_adds_points_on_new_transaction()
    {
        $accountId = new AccountId($this->uuid);
        $transactionId = new \OpenLoyalty\Component\Account\Domain\TransactionId($this->uuid);
        $expected = new AddPoints($accountId, new AddPointsTransfer(
            new PointsTransferId($this->uuid),
            10,
            0,
            null,
            null,
            false,
            $transactionId
        ));

        $listener = new ApplyEarningRuleToTransactionListener(
            $this->getCommandBus($expected),
            $this->getAccountDetailsRepository(),
            $this->getUuidGenerator(),
            $this->getApplierForTransaction(10),
            $this->getPointsTransfersManager(10, 0, null, $transactionId)
        );

        $listener->onRegisteredTransaction(
            new CustomerAssignedToTransactionSystemEvent(
                new TransactionId($this->uuid),
                new CustomerId($this->uuid),
                0,
                0,
                '1234567890'
            )
        );
    }
}
