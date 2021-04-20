<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Infrastructure\Provider;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;

/**
 * Interface CustomerDetailsProviderInterface.
 */
interface CustomerDetailsProviderInterface
{
    /**
     * @param CustomerId $customerId
     *
     * @return null|CustomerDetails
     */
    public function getCustomerDetailsByCustomerId(CustomerId $customerId): ?CustomerDetails;
}
