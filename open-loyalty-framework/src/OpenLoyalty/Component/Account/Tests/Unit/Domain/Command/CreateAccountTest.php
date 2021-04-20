<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Account\Tests\Unit\Domain\Command;

use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Command\CreateAccount;
use OpenLoyalty\Component\Account\Domain\Event\AccountWasCreated;
use OpenLoyalty\Component\Account\Domain\CustomerId;

/**
 * Class CreateAccountTest.
 */
final class CreateAccountTest extends AccountCommandHandlerTest
{
    /**
     * @test
     */
    public function it_creates_new_account()
    {
        $accountId = new AccountId('00000000-0000-0000-0000-000000000000');
        $customerId = new CustomerId('00000000-1111-0000-0000-000000000000');
        $this->scenario
            ->withAggregateId((string) $accountId)
            ->given([])
            ->when(new CreateAccount($accountId, $customerId))
            ->then(array(
                new AccountWasCreated($accountId, $customerId),
            ));
    }
}
