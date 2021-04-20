<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Provider;

/**
 * Interface EarningRuleReturnCampaignBoughtProviderInterface.
 */
interface EarningRuleReturnCampaignBoughtProviderInterface
{
    /**
     * @param string $transactionId
     * @param string $customerId
     *
     * @return array
     */
    public function findByTransactionAndCustomer(string $transactionId, string $customerId): array;
}
