<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Returns;

use OpenLoyalty\Component\EarningRule\Domain\Returns\Model\ReturnContext;

/**
 * Class RefundEvaluator.
 */
class RefundEvaluator
{
    /**
     * @var RefundAlgorithmProvider
     */
    private $algorithmProvider;

    /**
     * RefundEvaluator constructor.
     *
     * @param RefundAlgorithmProvider $algorithmProvider
     */
    public function __construct(RefundAlgorithmProvider $algorithmProvider)
    {
        $this->algorithmProvider = $algorithmProvider;
    }

    /**
     * @param ReturnContext $refundContext
     */
    public function refundTransaction(ReturnContext $refundContext): void
    {
        $algorithms = $this->algorithmProvider->getAlgorithms();

        /** @var RefundAlgorithmInterface $algorithm */
        foreach ($algorithms as $algorithm) {
            $algorithm->evaluate($refundContext);
        }
    }
}
