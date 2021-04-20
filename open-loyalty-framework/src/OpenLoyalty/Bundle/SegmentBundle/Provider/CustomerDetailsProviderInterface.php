<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Provider;

use OpenLoyalty\Component\Segment\Domain\Model\CustomerDetails;

/**
 * Interface CustomerDetailsProviderInterface.
 */
interface CustomerDetailsProviderInterface
{
    /**
     * @param string[] $customerIds
     *
     * @return CustomerDetails[]
     */
    public function getCustomers(array $customerIds): array;
}
