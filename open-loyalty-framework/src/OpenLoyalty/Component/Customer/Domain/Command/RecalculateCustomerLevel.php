<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Command;

use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class RecalculateCustomerLevel.
 */
class RecalculateCustomerLevel extends CustomerCommand
{
    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * RecalculateCustomerLevel constructor.
     *
     * @param CustomerId $customerId
     * @param \DateTime  $date
     */
    public function __construct(CustomerId $customerId, \DateTime $date)
    {
        parent::__construct($customerId);
        $this->date = $date;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }
}
