<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Account\Tests\Unit\Domain\ReadModel;

use DateTime;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AccountDetailsTest.
 */
final class AccountDetailsTest extends TestCase
{
    /**
     * @test
     * @dataProvider getPointsTransfersDataProvider
     *
     * @param DateTime $sinceDate
     * @param array    $pointsTransfers
     * @param int      $expected
     */
    public function it_returns_earned_points_since_last_level_recalculation_date(
        DateTime $sinceDate,
        array $pointsTransfers,
        int $expected
    ): void {
        /** @var AccountId|MockObject $accountId */
        $accountId = $this->getMockBuilder(AccountId::class)->disableOriginalConstructor()->getMock();
        /** @var CustomerId|MockObject $customerId */
        $customerId = $this->getMockBuilder(CustomerId::class)->disableOriginalConstructor()->getMock();

        $accountDetails = new AccountDetails($accountId, $customerId);
        foreach ($pointsTransfers as $pointsTransfer) {
            $accountDetails->addPointsTransfer($pointsTransfer);
        }

        $this->assertEquals($expected, $accountDetails->getEarnedAmountSince($sinceDate));
    }

    /**
     * @return array
     */
    public function getPointsTransfersDataProvider(): array
    {
        return [
            [
                new DateTime('02-01-2018 09:00:00'),
                [
                    new AddPointsTransfer(
                        new PointsTransferId('00000000-0000-0000-0000-000000000001'),
                        1,
                        null,
                        null,
                        new DateTime('01-01-2018 11:00:00')
                    ),
                    new AddPointsTransfer(
                        new PointsTransferId('00000000-0000-0000-0000-000000000002'),
                        2,
                        null,
                        null,
                        new DateTime('02-01-2018 10:00:00')
                    ),
                    new AddPointsTransfer(
                        new PointsTransferId('00000000-0000-0000-0000-000000000003'),
                        3,
                        null,
                        null,
                        new DateTime('03-01-2018 11:00:00')
                    ),
                ],
                5,
            ],
        ];
    }
}
