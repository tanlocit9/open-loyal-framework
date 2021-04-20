<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\ResponseModel;

/**
 * Class CouponUsageResponse.
 */
class CouponUsageResponse
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $used;

    /**
     * @var string
     */
    private $campaignId;

    /**
     * @var string
     */
    private $customerId;

    /**
     * CouponUsageResponse constructor.
     *
     * @param string $name
     * @param bool   $used
     * @param string $campaignId
     * @param string $customerId
     */
    public function __construct(
        string $name,
        bool $used,
        string $campaignId,
        string $customerId
    ) {
        $this->name = $name;
        $this->used = $used;
        $this->campaignId = $campaignId;
        $this->customerId = $customerId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isUsed(): bool
    {
        return $this->used;
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
    public function getCustomerId(): string
    {
        return $this->customerId;
    }
}
