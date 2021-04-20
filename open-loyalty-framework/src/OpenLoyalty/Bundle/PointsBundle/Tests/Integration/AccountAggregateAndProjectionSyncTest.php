<?php

namespace OpenLoyalty\Bundle\PointsBundle\Tests\Integration;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;
use OpenLoyalty\Component\Account\Domain\Account;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AccountAggregateAndProjectionSyncTest.
 */
class AccountAggregateAndProjectionSyncTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_projects_the_same_data_as_aggregate_contains()
    {
        static::$kernel->boot();
        $container = static::$kernel->getContainer();
        $projection = $this->getAccountByCustomerId(LoadUserData::USER_USER_ID);
        $aggregateRepo = $container->get('oloy.points.account.repository');
        /** @var Account $aggregate */
        $aggregate = $aggregateRepo->load($projection->getAccountId()->__toString());
        $this->assertEquals($aggregate->getAvailableAmount(), $projection->getAvailableAmount());
    }

    /**
     * @param int $customerId
     *
     * @return AccountDetails
     */
    protected function getAccountByCustomerId($customerId)
    {
        /** @var Repository $repo */
        $repo = static::$kernel->getContainer()->get('oloy.points.account.repository.account_details');
        $accounts = $repo->findBy(['customerId' => $customerId]);
        /** @var AccountDetails $account */
        $account = reset($accounts);

        return $account;
    }
}
