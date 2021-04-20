<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Import;

use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;

/**
 * Interface AccountProviderInterface.
 */
interface AccountProviderInterface
{
    /**
     * @param string|null $customerId
     * @param string|null $email
     * @param string|null $phone
     * @param string|null $loyaltyNumber
     *
     * @return null|AccountDetails
     */
    public function provideOne(?string $customerId, ?string $email, ?string $phone, ?string $loyaltyNumber): ?AccountDetails;
}
