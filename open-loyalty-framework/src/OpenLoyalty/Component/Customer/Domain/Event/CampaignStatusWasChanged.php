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
 * Class CampaignStatusWasChanged.
 */
class CampaignStatusWasChanged extends CustomerEvent
{
    /**
     * @var CampaignId
     */
    protected $campaignId;

    /**
     * @var Coupon
     */
    protected $coupon;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var null|TransactionId
     */
    private $transactionId;

    /**
     * CampaignStatusWasChanged constructor.
     *
     * @param CustomerId         $customerId
     * @param CampaignId         $campaignId
     * @param Coupon             $coupon
     * @param string             $status
     * @param null|TransactionId $transactionId
     */
    public function __construct(
        CustomerId $customerId,
        CampaignId $campaignId,
        Coupon $coupon,
        string $status,
        ?TransactionId $transactionId = null
    ) {
        parent::__construct($customerId);
        $this->campaignId = $campaignId;
        $this->status = $status;
        $this->coupon = $coupon;
        $this->transactionId = $transactionId;
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
                'status' => $this->status,
                'coupon' => $this->coupon->getCode(),
                'couponId' => $this->coupon->getId(),
                'transactionId' => $this->transactionId ? $this->transactionId->__toString() : null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new self(
            new CustomerId($data['customerId']),
            new CampaignId($data['campaignId']),
            new Coupon($data['couponId'], $data['coupon']),
            $data['status'],
            isset($data['transactionId']) ? new TransactionId($data['transactionId']) : null
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
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return Coupon
     */
    public function getCoupon(): Coupon
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
}
