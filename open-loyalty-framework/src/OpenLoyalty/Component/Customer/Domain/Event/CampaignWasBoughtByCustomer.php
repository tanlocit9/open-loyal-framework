<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Event;

use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\TransactionId;

/**
 * Class CampaignWasBoughtByCustomer.
 */
class CampaignWasBoughtByCustomer extends CustomerEvent
{
    /**
     * @var CampaignId
     */
    protected $campaignId;

    /**
     * @var \DateTime
     */
    protected $createdAt;

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
    protected $campaignName;

    /**
     * @var string
     */
    protected $reward;

    /**
     * @var string
     */
    protected $status = CampaignPurchase::STATUS_ACTIVE;

    /**
     * @var \DateTime|null
     */
    protected $activeSince = null;

    /**
     * @var \DateTime|null
     */
    protected $activeTo = null;

    /**
     * @var null|Identifier
     */
    private $transactionId = null;

    /**
     * CampaignWasBoughtByCustomer constructor.
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
        $this->createdAt = new \DateTime();
        $this->createdAt->setTimestamp(time());
        $this->costInPoints = $costInPoints;
        $this->coupon = $coupon;
        $this->campaignName = $campaignName;
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
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return array_merge(
            parent::serialize(),
            [
                'campaignId' => $this->campaignId->__toString(),
                'costInPoints' => $this->costInPoints,
                'createdAt' => $this->createdAt->getTimestamp(),
                'coupon' => $this->coupon->getCode(),
                'couponId' => $this->coupon->getId(),
                'campaignName' => $this->campaignName,
                'reward' => $this->reward,
                'status' => $this->status,
                'activeSince' => $this->activeSince ? $this->activeSince->getTimestamp() : null,
                'activeTo' => $this->activeTo ? $this->activeTo->getTimestamp() : null,
                'transactionId' => $this->transactionId ? $this->transactionId->__toString() : null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        if (isset($data['activeSince'])) {
            $activeSince = new \DateTime();
            $activeSince->setTimestamp($data['activeSince']);
        }

        if (isset($data['activeTo'])) {
            $activeTo = new \DateTime();
            $activeTo->setTimestamp($data['activeTo']);
        }

        $bought = new self(
            new CustomerId($data['customerId']),
            new CampaignId($data['campaignId']),
            $data['campaignName'],
            $data['costInPoints'],
            new Coupon($data['couponId'], $data['coupon']),
            $data['reward'],
            $data['status'] ?? CampaignPurchase::STATUS_ACTIVE,
            $activeSince ?? null,
            $activeTo ?? null,
            isset($data['transactionId']) ? new TransactionId($data['transactionId']) : null
        );

        $date = new \DateTime();
        $date->setTimestamp($data['createdAt']);
        $bought->createdAt = $date;

        return $bought;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
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
    public function getTransactionId(): ?Identifier
    {
        return $this->transactionId;
    }
}
