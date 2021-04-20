<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Domain\Specification;

/**
 * Interface UsernameSpecificationInterface.
 */
interface CustomerPhoneSpecificationInterface
{
    /**
     * @param string      $phoneNumber
     * @param string|null $customerId
     *
     * @return bool
     */
    public function isSatisfiedBy(string $phoneNumber, ?string $customerId = null): bool;
}
