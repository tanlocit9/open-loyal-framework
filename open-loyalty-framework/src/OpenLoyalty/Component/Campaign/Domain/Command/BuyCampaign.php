<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Command;

use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CustomerId;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use OpenLoyalty\Component\Campaign\Domain\TransactionId;

/**
 * Class BuyCampaign.
 */
class BuyCampaign
{
    /**
     * @var CampaignId
     */
    private $campaignId;

    /**
     * @var CustomerId
     */
    private $customerId;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var float
     */
    private $pointsValue;

    /**
     * @var TransactionId
     */
    private $transactionId;

    /**
     * BuyCampaign constructor.
     *
     * @param CampaignId         $campaignId
     * @param CustomerId         $customerId
     * @param Coupon             $coupon
     * @param float              $pointsValue
     * @param TransactionId|null $transactionId
     */
    public function __construct(
        CampaignId $campaignId,
        CustomerId $customerId,
        Coupon $coupon,
        float $pointsValue = null,
        TransactionId $transactionId = null
    ) {
        $this->campaignId = $campaignId;
        $this->customerId = $customerId;
        $this->coupon = $coupon;
        $this->pointsValue = $pointsValue;
        $this->transactionId = $transactionId;
    }

    /**
     * @return CampaignId
     */
    public function getCampaignId(): CampaignId
    {
        return $this->campaignId;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return Coupon
     */
    public function getCoupon(): Coupon
    {
        return $this->coupon;
    }

    /**
     * @return float|null
     */
    public function getPointsValue(): ?float
    {
        return $this->pointsValue;
    }

    /**
     * @return TransactionId|null
     */
    public function getTransactionId(): ?TransactionId
    {
        return $this->transactionId;
    }
}
