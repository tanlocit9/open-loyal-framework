<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Model;

use Broadway\Serializer\Serializable;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\TransactionId;

/**
 * Class CampaignPurchase.
 */
class CampaignPurchase implements Serializable
{
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const DELIVERY_STATUS_ORDERED = 'ordered';
    public const DELIVERY_STATUS_CANCELED = 'canceled';
    public const DELIVERY_STATUS_SHIPPED = 'shipped';
    public const DELIVERY_STATUS_DELIVERED = 'delivered';

    public const DELIVERY_STATUS_DEFAULT = self::DELIVERY_STATUS_ORDERED;

    /**
     * @var \DateTime
     */
    protected $purchaseAt;

    /**
     * @var float
     */
    protected $costInPoints;

    /**
     * @var CampaignId
     */
    protected $campaignId;

    /**
     * @var string
     */
    protected $reward;

    /**
     * @var string
     */
    protected $campaign;

    /**
     * @var bool
     */
    protected $used = false;

    /**
     * @var Coupon
     */
    protected $coupon;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var \DateTime|null
     */
    protected $activeSince;

    /**
     * @var \DateTime|null
     */
    protected $activeTo;

    /**
     * @var Identifier|null
     */
    private $transactionId;

    /**
     * @var TransactionId|null
     */
    private $usedForTransactionId;

    /**
     * @var float
     */
    private $returnedAmount = 0;

    /**
     * @var null|string
     */
    private $deliveryStatus = null;

    /**
     * @var null|\DateTime
     */
    private $usageDate = null;

    /**
     * CampaignPurchase constructor.
     *
     * @param \DateTime       $purchaseAt
     * @param int             $costInPoints
     * @param CampaignId      $campaignId
     * @param Coupon          $coupon
     * @param string          $reward
     * @param string          $status
     * @param \DateTime|null  $activeSince
     * @param \DateTime|null  $activeTo
     * @param Identifier|null $transactionId
     * @param string|null     $deliveryStatus
     */
    public function __construct(
        \DateTime $purchaseAt,
        $costInPoints,
        CampaignId $campaignId,
        Coupon $coupon,
        string $reward,
        string $status = self::STATUS_ACTIVE,
        ?\DateTime $activeSince = null,
        ?\DateTime $activeTo = null,
        ?Identifier $transactionId = null,
        ?string $deliveryStatus = self::DELIVERY_STATUS_DEFAULT
    ) {
        $this->purchaseAt = $purchaseAt;
        $this->costInPoints = $costInPoints;
        $this->campaignId = $campaignId;
        $this->coupon = $coupon;
        $this->reward = $reward;
        $this->status = $status;
        $this->activeSince = $activeSince;
        $this->activeTo = $activeTo;
        $this->transactionId = $transactionId;
        $this->deliveryStatus = $deliveryStatus;
    }

    /**
     * @param CustomerId $customerId
     *
     * @return string
     */
    public function getId(CustomerId $customerId): string
    {
        $transactionSuffix = $this->transactionId ? '_'.(string) $this->transactionId : '';

        return (string) $this->campaignId.'_'.(string) $customerId.'_'.$this->coupon->getCode().$transactionSuffix;
    }

    /**
     * @return \DateTime
     */
    public function getPurchaseAt()
    {
        return $this->purchaseAt;
    }

    /**
     * @return float
     */
    public function getCostInPoints()
    {
        return $this->costInPoints;
    }

    /**
     * @return CampaignId
     */
    public function getCampaignId()
    {
        return $this->campaignId;
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

        $date = new \DateTime();
        $date->setTimestamp($data['purchaseAt']);

        $purchase = new self(
            $date,
            $data['costInPoints'],
            new CampaignId($data['campaignId']),
            new Coupon($data['couponId'], $data['coupon']),
            $data['reward'],
            $data['status'] ?? self::STATUS_ACTIVE,
            $activeSince ?? null,
            $activeTo ?? null,
            isset($data['transactionId']) ? new TransactionId($data['transactionId']) : null,
            isset($data['deliveryStatus']) ? $data['deliveryStatus'] : self::DELIVERY_STATUS_DEFAULT
        );
        $usedFor = isset($data['usedForTransactionId']) ? new TransactionId($data['usedForTransactionId']) : null;
        $purchase->setUsedForTransactionId($usedFor);
        $purchase->setReturnedAmount(isset($data['returnedAmount']) ? $data['returnedAmount'] : 0);
        $purchase->setUsed($data['used']);

        $usageDate = null;
        if (isset($data['usageDate'])) {
            $usageDate = new \DateTime();
            $usageDate->setTimestamp($data['usageDate']);
        }
        $purchase->setUsageDate($usageDate);

        return $purchase;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return [
            'costInPoints' => $this->costInPoints,
            'purchaseAt' => $this->purchaseAt->getTimestamp(),
            'campaignId' => (string) $this->campaignId,
            'coupon' => $this->coupon->getCode(),
            'couponId' => $this->coupon->getId(),
            'used' => $this->used,
            'reward' => $this->reward,
            'isNotCashback' => $this->reward == Campaign::REWARD_TYPE_CASHBACK ? 0 : 1,
            'status' => $this->status,
            'activeSince' => $this->activeSince ? $this->activeSince->getTimestamp() : null,
            'activeTo' => $this->activeTo ? $this->activeTo->getTimestamp() : null,
            'transactionId' => $this->transactionId ? (string) $this->transactionId : null,
            'usedForTransactionId' => $this->usedForTransactionId ? (string) $this->usedForTransactionId : null,
            'returnedAmount' => $this->returnedAmount ?: 0,
            'deliveryStatus' => $this->deliveryStatus,
            'usageDate' => $this->usageDate ? $this->usageDate->getTimestamp() : null,
        ];
    }

    /**
     * @return bool
     */
    public function isUsed()
    {
        return $this->used;
    }

    /**
     * @return bool
     */
    public function canBeUsed(): bool
    {
        return self::STATUS_ACTIVE === $this->status && !$this->isUsed();
    }

    /**
     * @param bool $used
     */
    public function setUsed($used)
    {
        $this->used = $used;
    }

    /**
     * @return string
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param string $campaign
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * @return Coupon
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * @param Coupon $coupon
     */
    public function setCoupon(Coupon $coupon): void
    {
        $this->coupon = $coupon;
    }

    /**
     * @return string
     */
    public function getReward()
    {
        return $this->reward;
    }

    /**
     * @param string $reward
     */
    public function setReward($reward)
    {
        $this->reward = $reward;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
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
     * @return Identifier|null
     */
    public function getTransactionId(): ?Identifier
    {
        return $this->transactionId;
    }

    /**
     * @return null|TransactionId
     */
    public function getUsedForTransactionId(): ?TransactionId
    {
        return $this->usedForTransactionId;
    }

    /**
     * @param null|TransactionId $usedForTransactionId
     */
    public function setUsedForTransactionId(?TransactionId $usedForTransactionId): void
    {
        $this->usedForTransactionId = $usedForTransactionId;
    }

    /**
     * @return float
     */
    public function getReturnedAmount(): float
    {
        return $this->returnedAmount;
    }

    /**
     * @param float $returnedAmount
     */
    public function setReturnedAmount(float $returnedAmount = 0): void
    {
        $this->returnedAmount = $returnedAmount;
    }

    /**
     * @return null|string
     */
    public function getDeliveryStatus(): ?string
    {
        return $this->deliveryStatus;
    }

    /**
     * @param null|string $deliveryStatus
     */
    public function setDeliveryStatus(?string $deliveryStatus): void
    {
        $this->deliveryStatus = $deliveryStatus;
    }

    /**
     * @return \DateTime|null
     */
    public function getUsageDate(): ?\DateTime
    {
        return $this->usageDate;
    }

    /**
     * @param \DateTime|null $usageDate
     */
    public function setUsageDate(?\DateTime $usageDate): void
    {
        $this->usageDate = $usageDate;
    }
}
