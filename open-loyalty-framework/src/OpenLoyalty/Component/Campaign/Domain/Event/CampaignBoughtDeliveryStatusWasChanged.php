<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Domain\Event;

use Broadway\Serializer\Serializable;

/**
 * Class CampaignBoughtDeliveryStatusWasChangedEvent.
 */
class CampaignBoughtDeliveryStatusWasChanged implements Serializable
{
    /**
     * @var string
     */
    private $couponId;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $customerId;

    /**
     * CampaignBoughtDeliveryStatusWasChangedEvent constructor.
     *
     * @param string $couponId
     * @param string $customerId
     * @param string $status
     */
    public function __construct(string $couponId, string $customerId, string $status)
    {
        $this->couponId = $couponId;
        $this->status = $status;
        $this->customerId = $customerId;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data): self
    {
        return new self($data['couponId'], $data['customerId'], $data['status']);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return [
            'couponId' => $this->couponId,
            'customerId' => $this->customerId,
            'status' => $this->status,
        ];
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
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }
}
