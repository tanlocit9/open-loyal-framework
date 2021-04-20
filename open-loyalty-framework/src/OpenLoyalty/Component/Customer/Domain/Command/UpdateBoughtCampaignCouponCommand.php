<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Command;

/**
 * Class UpdateBoughtCampaignCouponCommand.
 */
class UpdateBoughtCampaignCouponCommand
{
    /**
     * @var string
     */
    private $customerId;

    /**
     * @var string
     */
    private $campaignId;

    /**
     * @var string|null
     */
    private $transactionId;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var string
     */
    private $newCoupon;

    /**
     * @var string
     */
    private $couponId;

    /**
     * UpdateBoughtCampaignCouponCommand constructor.
     *
     * @param string      $customerId
     * @param string      $campaignId
     * @param null|string $transactionId
     * @param \DateTime   $createdAt
     * @param string      $newCoupon
     * @param string      $couponId
     */
    public function __construct(
        string $customerId,
        string $campaignId,
        ?string $transactionId,
        \DateTime $createdAt,
        string $newCoupon,
        string $couponId
    ) {
        $this->customerId = $customerId;
        $this->campaignId = $campaignId;
        $this->transactionId = $transactionId;
        $this->createdAt = $createdAt;
        $this->newCoupon = $newCoupon;
        $this->couponId = $couponId;
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getCampaignId(): string
    {
        return $this->campaignId;
    }

    /**
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getNewCoupon(): string
    {
        return $this->newCoupon;
    }

    /**
     * @return string
     */
    public function getCouponId(): string
    {
        return $this->couponId;
    }
}
