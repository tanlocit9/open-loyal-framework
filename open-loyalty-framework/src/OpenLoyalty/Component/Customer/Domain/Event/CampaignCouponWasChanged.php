<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Event;

use OpenLoyalty\Component\Customer\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\TransactionId;

/**
 * Class CampaignCouponWasChanged.
 */
class CampaignCouponWasChanged extends CustomerEvent
{
    /**
     * @var CampaignId
     */
    private $campaignId;

    /**
     * @var TransactionId|null
     */
    private $transactionId;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var Coupon
     */
    private $newCoupon;

    /**
     * @var Coupon
     */
    private $oldCoupon;

    /**
     * CampaignCouponWasChanged constructor.
     *
     * @param CustomerId         $customerId
     * @param CampaignId         $campaignId
     * @param TransactionId|null $transactionId
     * @param \DateTime          $createdAt
     * @param Coupon             $newCoupon
     */
    public function __construct(
        CustomerId $customerId,
        CampaignId $campaignId,
        ?TransactionId $transactionId,
        \DateTime $createdAt,
        Coupon $newCoupon
    ) {
        parent::__construct($customerId);
        $this->campaignId = $campaignId;
        $this->transactionId = $transactionId;
        $this->createdAt = $createdAt;
        $this->newCoupon = $newCoupon;
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
                'transactionId' => $this->transactionId->__toString(),
                'createdAt' => $this->createdAt->getTimestamp(),
                'newCoupon' => $this->newCoupon->getCode(),
                'couponId' => $this->newCoupon->getId(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        $createdAt = new \DateTime();
        $createdAt->setTimestamp($data['createdAt']);

        return new self(
            new CustomerId($data['customerId']),
            new CampaignId($data['campaignId']),
            new TransactionId($data['transactionId']),
            $createdAt,
            new Coupon($data['couponId'], $data['newCoupon'])
        );
    }

    /**
     * @return CampaignId
     */
    public function getCampaignId(): CampaignId
    {
        return $this->campaignId;
    }

    /**
     * @return TransactionId|null
     */
    public function getTransactionId(): ?TransactionId
    {
        return $this->transactionId;
    }

    /**
     * @return Coupon
     */
    public function getOldCoupon(): Coupon
    {
        return $this->oldCoupon;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return Coupon
     */
    public function getNewCoupon(): Coupon
    {
        return $this->newCoupon;
    }
}
