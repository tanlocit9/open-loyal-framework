<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\ReadModel;

use Broadway\ReadModel\SerializableReadModel;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CustomerId;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use OpenLoyalty\Component\Core\Domain\ReadModel\Versionable;
use OpenLoyalty\Component\Core\Domain\ReadModel\VersionableReadModel;

/**
 * Class CouponUsage.
 */
class CouponUsage implements SerializableReadModel, VersionableReadModel
{
    use Versionable;

    /**
     * @var int
     */
    protected $usage = 0;

    /**
     * @var CampaignId
     */
    protected $campaignId;

    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var Coupon
     */
    protected $coupon;

    /**
     * CouponUsage constructor.
     *
     * @param CampaignId $campaignId
     * @param CustomerId $customerId
     * @param Coupon     $coupon
     * @param int        $usage
     */
    public function __construct(CampaignId $campaignId, CustomerId $customerId, Coupon $coupon, int $usage = 1)
    {
        $this->campaignId = $campaignId;
        $this->customerId = $customerId;
        $this->coupon = $coupon;
        $this->usage = $usage;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        if (isset($data['usage'])) {
            $usage = $data['usage'];
        } else {
            $usage = 1;
        }

        return new self(
            new CampaignId($data['campaignId']),
            new CustomerId($data['customerId']),
            new Coupon($data['coupon'], $data['couponId']),
            $usage
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return [
            'campaignId' => (string) $this->campaignId,
            'customerId' => (string) $this->customerId,
            'coupon' => $this->coupon->getCode(),
            'couponId' => $this->coupon->getId(),
            'usage' => $this->getUsage(),
        ];
    }

    /**
     * @param CampaignId $campaignId
     * @param CustomerId $customerId
     * @param Coupon     $coupon
     *
     * @return string
     */
    public static function createId(
        CampaignId $campaignId,
        CustomerId $customerId,
        Coupon $coupon
    ): string {
        $couponId = $coupon->getId() ? '_'.$coupon->getId() : '';

        return sprintf(
            '%s_%s_%s%s',
            (string) $campaignId,
            (string) $customerId,
            $coupon->getCode(),
            $couponId
        );
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return self::createId(
            $this->campaignId,
            $this->customerId,
            $this->coupon
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
     * @return int
     */
    public function getUsage(): int
    {
        return $this->usage;
    }
}
