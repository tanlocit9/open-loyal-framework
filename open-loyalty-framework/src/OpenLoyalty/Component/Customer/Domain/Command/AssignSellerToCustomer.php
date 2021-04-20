<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Command;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\SellerId;

/**
 * Class AssignSellerToCustomer.
 */
class AssignSellerToCustomer extends CustomerCommand
{
    /**
     * @var SellerId
     */
    protected $sellerId;

    public function __construct(CustomerId $customerId, SellerId $sellerId)
    {
        parent::__construct($customerId);
        $this->sellerId = $sellerId;
    }

    /**
     * @return SellerId
     */
    public function getSellerId()
    {
        return $this->sellerId;
    }
}
