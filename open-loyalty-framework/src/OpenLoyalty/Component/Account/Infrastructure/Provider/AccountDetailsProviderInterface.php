<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Account\Infrastructure\Provider;

use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Interface AccountDetailsProviderInterface.
 */
interface AccountDetailsProviderInterface
{
    /**
     * @param CustomerId $customerId
     *
     * @return null|AccountDetails
     */
    public function getAccountDetailsByCustomerId(CustomerId $customerId): ?AccountDetails;
}
