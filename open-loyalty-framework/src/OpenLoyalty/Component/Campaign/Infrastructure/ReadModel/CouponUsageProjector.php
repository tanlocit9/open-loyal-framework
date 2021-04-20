<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\ReadModel;

use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListener;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CustomerId;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CouponUsage;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CouponUsageRepository;
use OpenLoyalty\Component\Customer\Domain\Event\CampaignWasBoughtByCustomer;

/**
 * Class CouponUsageProjector.
 */
class CouponUsageProjector implements EventListener
{
    /**
     * @var CouponUsageRepository
     */
    protected $repository;

    /**
     * CouponUsageProjector constructor.
     *
     * @param CouponUsageRepository $repository
     */
    public function __construct(CouponUsageRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param CampaignWasBoughtByCustomer $event
     */
    protected function handleCampaignWasBoughtByCustomer(CampaignWasBoughtByCustomer $event): void
    {
        $this->storeCouponUsage(
            new CampaignId((string) $event->getCampaignId()),
            new CustomerId((string) $event->getCustomerId()),
            new Coupon($event->getCoupon()->getCode(), $event->getCoupon()->getId())
        );
    }

    /**
     * @param CampaignId $campaignId
     * @param CustomerId $customerId
     * @param Coupon     $coupon
     */
    public function storeCouponUsage(CampaignId $campaignId, CustomerId $customerId, Coupon $coupon)
    {
        $readModel = $this->getReadModel($campaignId, $customerId, $coupon);
        $this->repository->save($readModel);
    }

    /**
     * Remove all.
     */
    public function removeAll()
    {
        foreach ($this->repository->findAll() as $segmented) {
            $this->repository->remove($segmented->getId());
        }
    }

    /**
     * @param CampaignId $campaignId
     * @param CustomerId $customerId
     * @param Coupon     $coupon
     *
     * @return CouponUsage
     */
    private function getReadModel(CampaignId $campaignId, CustomerId $customerId, Coupon $coupon): CouponUsage
    {
        $couponId = CouponUsage::createId($campaignId, $customerId, $coupon);

        /** @var CouponUsage $readModel */
        $readModel = $this->repository->find($couponId);

        if (null === $readModel) {
            $readModel = new CouponUsage($campaignId, $customerId, $coupon, 1);
        } elseif (null !== $readModel->getUsage()) {
            $usage = $readModel->getUsage() + 1;
            $readModel = new CouponUsage($campaignId, $customerId, $coupon, $usage);
        }

        return $readModel;
    }

    /**
     * @param DomainMessage $domainMessage
     */
    public function handle(DomainMessage $domainMessage): void
    {
        $event = $domainMessage->getPayload();
        if ($event instanceof CampaignWasBoughtByCustomer) {
            $this->handleCampaignWasBoughtByCustomer($event);
        }
    }
}
