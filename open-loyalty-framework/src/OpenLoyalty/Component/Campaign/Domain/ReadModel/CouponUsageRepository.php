<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\ReadModel;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CustomerId;

/**
 * Interface CouponUsageRepository.
 */
interface CouponUsageRepository extends Repository
{
    /**
     * @param CampaignId $campaignId
     *
     * @return int
     */
    public function countUsageForCampaign(CampaignId $campaignId): int;

    /**
     * @param CampaignId $campaignId
     * @param CustomerId $customerId
     *
     * @return int
     */
    public function countUsageForCampaignAndCustomer(CampaignId $campaignId, CustomerId $customerId): int;

    /**
     * @param CampaignId $campaignId
     *
     * @return array
     */
    public function findByCampaign(CampaignId $campaignId): array;

    /**
     * @param CampaignId $campaignId
     * @param CustomerId $customerId
     * @param string     $couponCode
     *
     * @return int
     */
    public function countUsageForCampaignAndCustomerAndCode(
        CampaignId $campaignId,
        CustomerId $customerId,
        string $couponCode
    ): int;
}
