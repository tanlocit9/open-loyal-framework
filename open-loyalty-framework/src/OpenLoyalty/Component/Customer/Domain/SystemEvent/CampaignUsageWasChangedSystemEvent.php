<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Component\Customer\Domain\SystemEvent;

use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\TransactionId;

/**
 * Class CampaignUsageWasChangedSystemEvent.
 */
class CampaignUsageWasChangedSystemEvent extends CustomerSystemEvent
{
    /**
     * @var string
     */
    private $campaignId;

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
     * CampaignUsageWasChangedSystemEvent constructor.
     *
     * {@inheritdoc}
     *
     * @param CampaignId         $campaignId
     * @param Coupon             $coupon
     * @param TransactionId|null $transactionId
     * @param bool               $used
     */
    public function __construct(
        CustomerId $customerId,
        CampaignId $campaignId,
        Coupon $coupon,
        ?TransactionId $transactionId,
        bool $used
    ) {
        parent::__construct($customerId);
        $this->campaignId = $campaignId;
        $this->customerId = $customerId;
        $this->couponId = $coupon->getId();
        $this->couponCode = $coupon->getCode();
        $this->transactionId = $transactionId ?? null;
        $this->used = $used;
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
