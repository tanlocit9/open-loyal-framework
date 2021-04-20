<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Domain\Command;

use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;

/**
 * Class ReturnCustomerCampaign.
 */
class ReturnCustomerCampaign extends CustomerCommand
{
    /**
     * @var CampaignId
     */
    private $campaignId;

    /**
     * @var string
     */
    private $campaignName;

    /**
     * @var float
     */
    private $costInPoints;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var string
     */
    private $reward;

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
     * @var Identifier
     */
    private $transactionId;

    /**
     * @var string
     */
    private $purchaseId;

    /**
     * BuyCampaign constructor.
     *
     * @param CustomerId      $customerId
     * @param CampaignId      $campaignId
     * @param string          $campaignName
     * @param float           $costInPoints
     * @param Coupon          $coupon
     * @param string          $reward
     * @param Identifier|null $transactionId
     * @param string          $purchaseId
     * @param string          $status
     * @param \DateTime|null  $activeSince
     * @param \DateTime|null  $activeTo
     */
    public function __construct(
        CustomerId $customerId,
        CampaignId $campaignId,
        string $campaignName,
        float $costInPoints,
        Coupon $coupon,
        string $reward,
        Identifier $transactionId,
        string $purchaseId,
        string $status = CampaignPurchase::STATUS_ACTIVE,
        ?\DateTime $activeSince = null,
        ?\DateTime $activeTo = null
    ) {
        parent::__construct($customerId);
        $this->purchaseId = $purchaseId;
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
    public function getCampaignId(): CampaignId
    {
        return $this->campaignId;
    }

    /**
     * @return float
     */
    public function getCostInPoints(): float
    {
        return $this->costInPoints;
    }

    /**
     * @return Coupon
     */
    public function getCoupon(): Coupon
    {
        return $this->coupon;
    }

    /**
     * @return string
     */
    public function getCampaignName(): string
    {
        return $this->campaignName;
    }

    /**
     * @return string
     */
    public function getReward(): string
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
    public function getTransactionId(): Identifier
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function getPurchaseId(): string
    {
        return $this->purchaseId;
    }
}
