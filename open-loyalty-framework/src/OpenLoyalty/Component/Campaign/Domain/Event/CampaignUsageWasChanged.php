<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Domain\Event;

/**
 * Class CampaignUsageWasChanged.
 */
class CampaignUsageWasChanged
{
    /**
     * @var string
     */
    private $campaignId;

    /**
     * @var string
     */
    private $customerId;

    /**
     * @var string
     */
    private $couponId;

    /**
     * @var string
     */
    private $couponCode;

    /**
     * @var bool
     */
    private $used;

    /**
     * @var null|string
     */
    private $transactionId;

    /**
     * CampaignUsageWasChanged constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->campaignId = $data['campaignId'];
        $this->customerId = $data['customerId'];
        $this->couponId = $data['couponId'];
        $this->couponCode = $data['couponCode'];
        $this->transactionId = empty($data['transactionId']) ? null : $data['transactionId'];
        $this->used = $data['used'];
    }

    /**
     * @return string
     */
    public function getCampaignId(): string
    {
        return $this->campaignId;
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
    public function getCouponCode(): string
    {
        return $this->couponCode;
    }

    /**
     * @return bool
     */
    public function isUsed(): bool
    {
        return $this->used;
    }

    /**
     * @return null|string
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }
}
