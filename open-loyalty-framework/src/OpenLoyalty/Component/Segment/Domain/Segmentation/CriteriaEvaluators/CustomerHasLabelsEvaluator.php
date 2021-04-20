<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Segmentation\CriteriaEvaluators;

use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\CustomerHasLabels;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion;

/**
 * Class CustomerHasLabelsEvaluator.
 */
class CustomerHasLabelsEvaluator implements Evaluator
{
    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    /**
     * CustomerHasLabelsEvaluator constructor.
     *
     * @param CustomerDetailsRepository $customerDetailsRepository
     */
    public function __construct(
        CustomerDetailsRepository $customerDetailsRepository
    ) {
        $this->customerDetailsRepository = $customerDetailsRepository;
    }

    /**
     * @param Criterion $criterion
     *
     * @return array
     */
    public function evaluate(Criterion $criterion)
    {
        if (!$criterion instanceof CustomerHasLabels) {
            return [];
        }

        $customers = $this->customerDetailsRepository->findWithLabels($criterion->getLabels(), true);

        return array_map(function (CustomerDetails $customerDetails) {
            return $customerDetails->getId();
        }, $customers);
    }

    /**
     * @param Criterion $criterion
     *
     * @return bool
     */
    public function support(Criterion $criterion)
    {
        return $criterion instanceof CustomerHasLabels;
    }
}
