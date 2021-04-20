<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Segmentation\CriteriaEvaluators;

use OpenLoyalty\Component\Segment\Domain\Model\Criteria\BoughtSKUs;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;

/**
 * Class BoughtSKUsEvaluator.
 */
class BoughtSKUsEvaluator implements Evaluator
{
    /**
     * @var TransactionDetailsRepository
     */
    protected $transactionDetailsRepository;

    /**
     * @var CustomerValidator
     */
    protected $customerValidator;

    /**
     * BoughtInPosEvaluator constructor.
     *
     * @param TransactionDetailsRepository $transactionDetailsRepository
     * @param CustomerValidator            $customerValidator
     */
    public function __construct(TransactionDetailsRepository $transactionDetailsRepository, CustomerValidator $customerValidator)
    {
        $this->transactionDetailsRepository = $transactionDetailsRepository;
        $this->customerValidator = $customerValidator;
    }

    /**
     * @param Criterion $criterion
     *
     * @return array
     */
    public function evaluate(Criterion $criterion)
    {
        if (!$criterion instanceof BoughtSKUs) {
            return [];
        }

        $transactions = $this->transactionDetailsRepository->findBySKUs($criterion->getSkuIds());

        $customers = [];
        /** @var TransactionDetails $transaction */
        foreach ($transactions as $transaction) {
            if (!$this->customerValidator->isValid($transaction->getCustomerId())) {
                continue;
            }

            $customers[$transaction->getCustomerId()->__toString()] = $transaction->getCustomerId()->__toString();
        }

        return $customers;
    }

    /**
     * @param Criterion $criterion
     *
     * @return bool
     */
    public function support(Criterion $criterion)
    {
        return $criterion instanceof BoughtSKUs;
    }
}
