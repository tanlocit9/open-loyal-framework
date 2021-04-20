<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Command;

use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\TransactionId;

/**
 * Class ChangeCampaignUsage.
 */
class ChangeCampaignUsage extends CustomerCommand
{
    /**
     * @var CampaignId
     */
    private $campaignId;

    /**
     * @var bool
     */
    private $used;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var TransactionId|null
     */
    private $transactionId;

    /**
     * @var \DateTime|null
     */
    private $usageDate = null;

    /**
     * ChangeCampaignUsage constructor.
     *
     * @param CustomerId         $customerId
     * @param CampaignId         $campaignId
     * @param Coupon             $coupon
     * @param bool               $used
     * @param null|TransactionId $transactionId
     */
    public function __construct(
        CustomerId $customerId,
        CampaignId $campaignId,
        Coupon $coupon,
        bool $used,
        ?TransactionId $transactionId = null
    ) {
        parent::__construct($customerId);
        $this->campaignId = $campaignId;
        $this->used = $used;
        $this->coupon = $coupon;
        $this->transactionId = $transactionId;
        if (true === $this->used) {
            $this->usageDate = new \DateTime();
        }
    }

    /**
     * @return CampaignId
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }

    /**
     * @return bool
     */
    public function isUsed()
    {
        return $this->used;
    }

    /**
     * @return Coupon
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * @return null|TransactionId
     */
    public function getTransactionId(): ?TransactionId
    {
        return $this->transactionId;
    }

    /**
     * @return \DateTime|null
     */
    public function getUsageDate(): ?\DateTime
    {
        return $this->usageDate;
    }
}
