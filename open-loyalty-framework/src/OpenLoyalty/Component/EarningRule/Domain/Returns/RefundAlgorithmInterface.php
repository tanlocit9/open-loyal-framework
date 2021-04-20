<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Returns;

use OpenLoyalty\Component\EarningRule\Domain\Returns\Model\ReturnContext;

/**
 * Interface RefundAlgorithmInterface.
 */
interface RefundAlgorithmInterface
{
    /**
     * @param ReturnContext $refundContext
     *
     * @return bool
     */
    public function evaluate(ReturnContext $refundContext): bool;
}
