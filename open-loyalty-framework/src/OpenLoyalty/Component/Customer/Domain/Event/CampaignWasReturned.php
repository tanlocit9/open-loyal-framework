<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Domain\Event;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;

/**
 * Class CampaignWasReturned.
 */
class CampaignWasReturned extends CustomerEvent
{
    /**
     * @var string
     */
    private $purchaseId;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * CampaignCouponWasChanged constructor.
     *
     * @param CustomerId $customerId
     * @param string     $purchaseId
     * @param Coupon     $coupon
     */
    public function __construct(
        CustomerId $customerId,
        string $purchaseId,
        Coupon $coupon
    ) {
        parent::__construct($customerId);
        $this->purchaseId = $purchaseId;
        $this->coupon = $coupon;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return array_merge(
            parent::serialize(),
            [
                'purchaseId' => $this->purchaseId,
                'coupon' => $this->coupon->getCode(),
                'couponId' => $this->coupon->getId(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new self(
            new CustomerId($data['customerId']),
            $data['purchaseId'],
            new Coupon($data['couponId'], $data['coupon'])
        );
    }

    /**
     * @return string
     */
    public function getPurchaseId(): string
    {
        return $this->purchaseId;
    }

    /**
     * @return Coupon
     */
    public function getCoupon(): Coupon
    {
        return $this->coupon;
    }
}
