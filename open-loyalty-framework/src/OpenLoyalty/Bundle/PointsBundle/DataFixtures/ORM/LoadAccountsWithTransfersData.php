<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\DataFixtures\ORM;

use Broadway\CommandHandling\CommandBus;
use Broadway\ReadModel\Repository;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OpenLoyalty\Bundle\PointsBundle\Service\PointsTransfersManager;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Command\AddPoints;
use OpenLoyalty\Component\Account\Domain\Command\ExpirePointsTransfer;
use OpenLoyalty\Component\Account\Domain\Command\SpendPoints;
use OpenLoyalty\Component\Account\Domain\Model\SpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Account\Infrastructure\PointsTransferManagerInterface;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;

/**
 * Class LoadAccountsWithTransfersData.
 */
class LoadAccountsWithTransfersData extends ContainerAwareFixture implements OrderedFixtureInterface
{
    const ACCOUNT2_ID = 'e82c96cf-32a3-43bd-9034-4df343e5fd92';
    const POINTS_ID = 'e82c96cf-32a3-43bd-9034-4df343e5f111';
    const POINTS22_ID = 'e82c96cf-32a3-43bd-9034-4df343e5f211';
    const POINTS222_ID = 'e82c96cf-32a3-43bd-9034-4df343e5f222';
    const POINTS2_ID = 'e82c96cf-32a3-43bd-9034-4df343e5f222';
    const POINTS3_ID = 'e82c96cf-32a3-43bd-9034-4df343e5f333';
    const POINTS4_ID = 'e82c96cf-32a3-43bd-9034-4df343e5f433';
    const POINTS5_ID = 'e82c96cf-32a3-43bd-9034-4df343e5f435';
    const POINTS6_ID = 'e82c96cf-32a3-43bd-9034-4df343e5f436';

    public function load(ObjectManager $manager): void
    {
        /** @var PointsTransferManagerInterface $pointsTransferManager */
        $pointsTransferManager = $this->container->get(PointsTransfersManager::class);

        /** @var CommandBus $commandBus */
        $commandBus = $this->container->get('broadway.command_handling.command_bus');

        $accountId = $this->getAccountIdByCustomerId(LoadUserData::TEST_USER_ID);
        $account2Id = $this->getAccountIdByCustomerId(LoadUserData::USER_USER_ID);

        $accountForTransfer1 = $this->getAccountIdByCustomerId(LoadUserData::USER_TRANSFER_2_USER_ID);
        $accountForTransfer2 = $this->getAccountIdByCustomerId(LoadUserData::USER_TRANSFER_3_USER_ID);
        $accountForCoupons = $this->getAccountIdByCustomerId(LoadUserData::USER_COUPON_RETURN_ID);

        $commandBus->dispatch(
            new AddPoints(
                new AccountId($accountForTransfer1),
                $pointsTransferManager->createAddPointsTransferInstance(
                    new PointsTransferId(static::POINTS5_ID),
                    100
                )
            )
        );

        $commandBus->dispatch(
            new AddPoints(
                new AccountId($accountForTransfer2),
                $pointsTransferManager->createAddPointsTransferInstance(
                    new PointsTransferId(static::POINTS6_ID),
                    100
                )
            )
        );

        $commandBus->dispatch(
            new AddPoints(
                new AccountId($accountId),
                $pointsTransferManager->createAddPointsTransferInstance(
                    new PointsTransferId(static::POINTS_ID),
                    100,
                    new \DateTime('-29 days')
                )
            )
        );

        $commandBus->dispatch(
            new AddPoints(
                new AccountId($account2Id),
                $pointsTransferManager->createAddPointsTransferInstance(
                    new PointsTransferId(static::POINTS22_ID),
                    100,
                    new \DateTime('-29 days')
                )
            )
        );

        $commandBus->dispatch(
            new AddPoints(
                new AccountId($accountId),
                $pointsTransferManager->createAddPointsTransferInstance(
                    new PointsTransferId(static::POINTS4_ID),
                    100,
                    new \DateTime('-29 days')
                )
            )
        );

        $commandBus->dispatch(
            new AddPoints(
                new AccountId($accountForCoupons),
                $pointsTransferManager->createAddPointsTransferInstance(
                    new PointsTransferId(static::POINTS222_ID),
                    1000,
                    new \DateTime('-3 days')
                )
            )
        );

        $commandBus->dispatch(
            new SpendPoints(
                new AccountId($accountId),
                new SpendPointsTransfer(
                    new PointsTransferId(static::POINTS3_ID),
                    100,
                    null,
                    false,
                    'Example comment'
                )
            )
        );

        $commandBus->dispatch(
            new ExpirePointsTransfer(new AccountId($accountId), new PointsTransferId(static::POINTS_ID))
        );
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder(): int
    {
        return 10;
    }

    /**
     * @param int $customerId
     *
     * @return string
     */
    private function getAccountIdByCustomerId(string $customerId): string
    {
        /** @var Repository $accountDetailsRepository */
        $accountDetailsRepository = $this->container->get('oloy.points.account.repository.account_details');

        $accounts = $accountDetailsRepository->findBy(['customerId' => $customerId]);

        /** @var AccountDetails $account */
        $account = reset($accounts);

        return (string) $account->getAccountId();
    }
}
