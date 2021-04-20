<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Returns\Model;

use OpenLoyalty\Component\EarningRule\Domain\CustomerId;
use OpenLoyalty\Component\EarningRule\Domain\TransactionId;

/**
 * Class RefundContext.
 */
class ReturnContext
{
    /**
     * @var TransactionId
     */
    private $refundTransactionId;

    /**
     * @var TransactionId
     */
    private $baseTransactionId;

    /**
     * @var CustomerId
     */
    private $customerId;

    /**
     * @var Campaign[]
     */
    private $campaigns;

    /**
     * RefundContext constructor.
     *
     * @param TransactionId $refundTransactionId
     * @param TransactionId $baseTransactionId
     * @param CustomerId    $customerId
     * @param array         $campaigns
     */
    public function __construct(
        TransactionId $refundTransactionId,
        TransactionId $baseTransactionId,
        CustomerId $customerId,
        array $campaigns
    ) {
        $this->refundTransactionId = $refundTransactionId;
        $this->baseTransactionId = $baseTransactionId;
        $this->campaigns = $campaigns;
        $this->customerId = $customerId;
    }

    /**
     * @return TransactionId
     */
    public function getRefundTransactionId(): TransactionId
    {
        return $this->refundTransactionId;
    }

    /**
     * @return TransactionId
     */
    public function getBaseTransactionId(): TransactionId
    {
        return $this->baseTransactionId;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return Campaign[]
     */
    public function getCampaigns(): array
    {
        return $this->campaigns;
    }
}
