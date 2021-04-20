<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Command;

use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;

/**
 * Class BuyCustomerCampaign.
 */
class BuyCustomerCampaign extends CustomerCommand
{
    /**
     * @var CampaignId
     */
    protected $campaignId;

    /**
     * @var string
     */
    protected $campaignName;

    /**
     * @var float
     */
    protected $costInPoints;

    /**
     * @var Coupon
     */
    protected $coupon;

    /**
     * @var string
     */
    protected $reward;

    /**
     * @var string
     */
    private $status;

    /**
     * @var \DateTime|null
     */
    private $activeSince;

    /**
     * @var \DateTime|null
     */
    private $activeTo;

    /**
     * @var Identifier|null
     */
    private $transactionId;

    /**
     * BuyCampaign constructor.
     *
     * @param CustomerId $customerId
     * @param CampaignId $campaignId
     * @param $campaignName
     * @param $costInPoints
     * @param Coupon          $coupon
     * @param string          $reward
     * @param string          $status
     * @param \DateTime|null  $activeSince
     * @param \DateTime|null  $activeTo
     * @param Identifier|null $transactionId
     */
    public function __construct(
        CustomerId $customerId,
        CampaignId $campaignId,
        $campaignName,
        $costInPoints,
        Coupon $coupon,
        string $reward,
        string $status = CampaignPurchase::STATUS_ACTIVE,
        ?\DateTime $activeSince = null,
        ?\DateTime $activeTo = null,
        ?Identifier $transactionId = null
    ) {
        parent::__construct($customerId);
        $this->campaignId = $campaignId;
        $this->campaignName = $campaignName;
        $this->costInPoints = $costInPoints;
        $this->coupon = $coupon;
        $this->reward = $reward;
        $this->status = $status;
        $this->activeSince = $activeSince;
        $this->activeTo = $activeTo;
        $this->transactionId = $transactionId;
    }

    /**
     * @return CampaignId
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }

    /**
     * @return float
     */
    public function getCostInPoints()
    {
        return $this->costInPoints;
    }

    /**
     * @return Coupon
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * @return string
     */
    public function getCampaignName()
    {
        return $this->campaignName;
    }

    /**
     * @return string
     */
    public function getReward()
    {
        return $this->reward;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return \DateTime|null
     */
    public function getActiveSince(): ?\DateTime
    {
        return $this->activeSince;
    }

    /**
     * @return \DateTime|null
     */
    public function getActiveTo(): ?\DateTime
    {
        return $this->activeTo;
    }

    /**
     * @return null|Identifier
     */
    public function getTransactionId(): ?Identifier
    {
        return $this->transactionId;
    }
}
