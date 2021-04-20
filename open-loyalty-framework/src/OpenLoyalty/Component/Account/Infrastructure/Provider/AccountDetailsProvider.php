<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Account\Infrastructure\Provider;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class AccountDetailsProvider.
 */
class AccountDetailsProvider implements AccountDetailsProviderInterface
{
    /**
     * @var Repository
     */
    private $accountDetailsRepository;

    /**
     * AccountDetailsProvider constructor.
     *
     * @param Repository $accountDetailsRepository
     */
    public function __construct(Repository $accountDetailsRepository)
    {
        $this->accountDetailsRepository = $accountDetailsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccountDetailsByCustomerId(CustomerId $customerId): ?AccountDetails
    {
        $accounts = $this->accountDetailsRepository->findBy(['customerId' => (string) $customerId]);

        if (0 === count($accounts)) {
            return null;
        }

        /** @var AccountDetails $account */
        $account = reset($accounts);

        if (!$account instanceof AccountDetails) {
            return null;
        }

        return $account;
    }
}
