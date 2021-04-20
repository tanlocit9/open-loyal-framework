<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\Provider;

/**
 * Interface ParentTransactionIdProviderInterface.
 */
interface ParentTransactionIdProviderInterface
{
    /**
     * @param string $childTransactionId
     *
     * @return string|null
     */
    public function findParentTransactionId(string $childTransactionId): ?string;
}
