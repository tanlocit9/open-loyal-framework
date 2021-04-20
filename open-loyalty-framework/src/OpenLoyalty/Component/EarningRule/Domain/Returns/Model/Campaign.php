<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Returns\Model;

use OpenLoyalty\Component\EarningRule\Domain\CampaignId;
use OpenLoyalty\Component\EarningRule\Domain\Coupon;

/**
 * Class Campaign.
 */
class Campaign
{
    /**
     * @var CampaignId
     */
    private $campaignId;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \DateTime
     */
    private $createdDate;

    /**
     * @var string
     */
    private $status;

    /**
     * Campaign constructor.
     *
     * @param CampaignId $campaignId
     * @param Coupon     $coupon
     * @param string     $type
     * @param \DateTime  $createdDate
     * @param string     $status
     */
    public function __construct(
        CampaignId $campaignId,
        Coupon $coupon,
        string $type,
        \DateTime $createdDate,
        string $status
    ) {
        $this->campaignId = $campaignId;
        $this->coupon = $coupon;
        $this->type = $type;
        $this->createdDate = $createdDate;
        $this->status = $status;
    }

    /**
     * @return CampaignId
     */
    public function getCampaignId(): CampaignId
    {
        return $this->campaignId;
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDate(): \DateTime
    {
        return $this->createdDate;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
