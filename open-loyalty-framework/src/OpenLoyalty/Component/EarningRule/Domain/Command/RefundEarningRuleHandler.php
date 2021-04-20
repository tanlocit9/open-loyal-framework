<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use OpenLoyalty\Component\Campaign\Domain\CustomerId as CampaignCustomerId;
use OpenLoyalty\Component\Campaign\Domain\Provider\EarningRuleReturnCampaignBoughtProviderInterface;
use OpenLoyalty\Component\Campaign\Domain\TransactionId as CampaignTransactionId;
use OpenLoyalty\Component\EarningRule\Domain\CustomerId;
use OpenLoyalty\Component\EarningRule\Domain\Returns\Model\ReturnContext;
use OpenLoyalty\Component\EarningRule\Domain\Returns\RefundEvaluator;
use OpenLoyalty\Component\EarningRule\Domain\TransactionId;
use OpenLoyalty\Component\Transaction\Domain\Provider\ParentTransactionIdProviderInterface;

/**
 * Class RefundEarningRuleHandler.
 */
class RefundEarningRuleHandler extends SimpleCommandHandler
{
    /**
     * @var ParentTransactionIdProviderInterface
     */
    private $parentTransactionIdProvider;

    /**
     * @var RefundEvaluator
     */
    private $refundEvaluator;

    /**
     * @var EarningRuleReturnCampaignBoughtProviderInterface
     */
    private $campaignBoughtProvider;

    /**
     * RefundEarningRuleHandler constructor.
     *
     * @param ParentTransactionIdProviderInterface             $parentTransactionIdProvider
     * @param RefundEvaluator                                  $refundEvaluator
     * @param EarningRuleReturnCampaignBoughtProviderInterface $campaignBoughtProvider
     */
    public function __construct(
        ParentTransactionIdProviderInterface $parentTransactionIdProvider,
        RefundEvaluator $refundEvaluator,
        EarningRuleReturnCampaignBoughtProviderInterface $campaignBoughtProvider
    ) {
        $this->parentTransactionIdProvider = $parentTransactionIdProvider;
        $this->refundEvaluator = $refundEvaluator;
        $this->campaignBoughtProvider = $campaignBoughtProvider;
    }

    /**
     * @param RefundEarningRuleCommand $command
     */
    public function handleRefundEarningRuleCommand(RefundEarningRuleCommand $command)
    {
        $parentTransactionId = $this->parentTransactionIdProvider->findParentTransactionId($command->getTransactionId());
        if ($parentTransactionId === null) {
            return;
        }

        $campaigns = $this->campaignBoughtProvider->findByTransactionAndCustomer(
            new CampaignTransactionId($parentTransactionId),
            new CampaignCustomerId($command->getCustomerId())
        );

        $context = new ReturnContext(
            new TransactionId($command->getTransactionId()),
            new TransactionId($parentTransactionId),
            new CustomerId($command->getCustomerId()),
            $campaigns
        );

        $this->refundEvaluator->refundTransaction($context);
    }
}
