<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Level\Infrastructure\Provider;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Level\Domain\Level;

/**
 * Interface NextLevelProviderInterface.
 */
interface NextLevelProviderInterface
{
    /**
     * @param CustomerId $customerId
     *
     * @return null|Level
     */
    public function getNextLevelForCustomerId(CustomerId $customerId): ?Level;
}
