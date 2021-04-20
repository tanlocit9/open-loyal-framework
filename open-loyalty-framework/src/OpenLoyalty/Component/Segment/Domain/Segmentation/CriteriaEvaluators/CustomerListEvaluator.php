<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Segmentation\CriteriaEvaluators;

use OpenLoyalty\Component\Segment\Domain\Model\Criteria\CustomerList;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion;

/**
 * Class CustomerListEvaluator.
 */
class CustomerListEvaluator implements Evaluator
{
    /**
     * @param Criterion $criterion
     *
     * @return array
     */
    public function evaluate(Criterion $criterion)
    {
        if (!$this->support($criterion)) {
            return [];
        }

        return $criterion->getCustomers();
    }

    /**
     * @param Criterion $criterion
     *
     * @return bool
     */
    public function support(Criterion $criterion)
    {
        return $criterion instanceof CustomerList;
    }
}
