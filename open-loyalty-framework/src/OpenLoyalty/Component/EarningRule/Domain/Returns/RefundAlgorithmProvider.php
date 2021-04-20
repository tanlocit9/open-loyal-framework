<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Returns;

/**
 * Class RefundAlgorithmProvider.
 */
class RefundAlgorithmProvider
{
    /**
     * @var RefundAlgorithmInterface[]
     */
    private $refundAlgorithms = [];

    /**
     * RefundAlgorithmProvider constructor.
     *
     * @param iterable $refundAlgorithms
     */
    public function __construct(iterable $refundAlgorithms)
    {
        foreach ($refundAlgorithms as $refundAlgorithm) {
            if ($refundAlgorithm instanceof RefundAlgorithmInterface) {
                $this->refundAlgorithms[] = $refundAlgorithm;
            }
        }
    }

    /**
     * @return RefundAlgorithmInterface[]
     */
    public function getAlgorithms(): array
    {
        return $this->refundAlgorithms;
    }
}
