<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\Repository;

use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CustomerId;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CouponUsage;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CouponUsageRepository;
use OpenLoyalty\Component\Core\Infrastructure\Repository\OloyElasticsearchRepository;

/**
 * Class CouponUsageElasticsearchRepository.
 */
class CouponUsageElasticsearchRepository extends OloyElasticsearchRepository implements CouponUsageRepository
{
    /**
     * {@inheritdoc}
     */
    public function countUsageForCampaign(CampaignId $campaignId): int
    {
        $total = 0;
        $usages = $this->findBy(['campaignId' => (string) $campaignId]);
        /** @var CouponUsage $usage */
        foreach ($usages as $usage) {
            $total += $usage->getUsage();
        }

        return $total;
    }

    /**
     * {@inheritdoc}
     */
    public function countUsageForCampaignAndCustomer(CampaignId $campaignId, CustomerId $customerId): int
    {
        $total = 0;
        $all = $this->findBy([
            'campaignId' => (string) $campaignId,
            'customerId' => (string) $customerId,
        ]);

        /** @var CouponUsage $usage */
        foreach ($all as $usage) {
            $total += $usage->getUsage();
        }

        return $total;
    }

    /**
     * {@inheritdoc}
     */
    public function countUsageForCampaignAndCustomerAndCode(
        CampaignId $campaignId,
        CustomerId $customerId,
        string $couponCode
    ): int {
        $total = 0;
        $query = [
            'bool' => [
                'must' => [
                    [
                        'term' => [
                            'campaignId' => (string) $campaignId,
                        ],
                    ],
                    [
                        'term' => [
                            'customerId' => (string) $customerId,
                        ],
                    ],
                    [
                        'match' => [
                            'coupon' => $couponCode,
                        ],
                    ],
                ],
            ],
        ];

        $all = $this->query($query);

        /** @var CouponUsage $usage */
        foreach ($all as $usage) {
            $total += $usage->getUsage();
        }

        return $total;
    }

    /**
     * {@inheritdoc}
     */
    public function findByCampaign(CampaignId $campaignId): array
    {
        return $this->findBy(['campaignId' => (string) $campaignId]);
    }
}
