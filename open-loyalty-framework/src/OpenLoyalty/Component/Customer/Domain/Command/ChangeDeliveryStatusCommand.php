<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Domain\Command;

use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class ChangeDeliveryStatusCommand.
 */
class ChangeDeliveryStatusCommand
{
    /**
     * @var string
     */
    private $couponId;

    /**
     * @var string
     */
    private $customerId;

    /**
     * @var string
     */
    private $status;

    /**
     * ChangeStatusRewardsRedeemedCommand constructor.
     *
     * @param string $couponId
     * @param string $customerId
     * @param string $status
     */
    public function __construct(string $couponId, string $customerId, string $status)
    {
        $this->couponId = $couponId;
        $this->customerId = $customerId;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getCouponId(): string
    {
        return $this->couponId;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
    {
        return new CustomerId($this->customerId);
    }
}
