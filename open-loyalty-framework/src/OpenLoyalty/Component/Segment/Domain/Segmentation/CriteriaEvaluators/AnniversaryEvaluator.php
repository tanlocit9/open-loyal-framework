<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Segmentation\CriteriaEvaluators;

use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Segment\Domain\Model\Criteria\Anniversary;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion;

/**
 * Class AnniversaryEvaluator.
 */
class AnniversaryEvaluator implements Evaluator
{
    /**
     * @var CustomerDetailsRepository
     */
    protected $customerDetailsRepository;

    /**
     * AnniversaryEvaluator constructor.
     *
     * @param CustomerDetailsRepository $customerDetailsRepository
     */
    public function __construct(CustomerDetailsRepository $customerDetailsRepository)
    {
        $this->customerDetailsRepository = $customerDetailsRepository;
    }

    /**
     * @param Criterion $criterion
     *
     * @return array
     */
    public function evaluate(Criterion $criterion)
    {
        if (!$criterion instanceof Anniversary) {
            return [];
        }
        $from = new \DateTime('today');
        $to = new \DateTime(sprintf('today +%d days', $criterion->getDays()));

        if ($criterion->getAnniversaryType() == Anniversary::TYPE_BIRTHDAY) {
            $customers = $this->customerDetailsRepository->findByBirthdayAnniversary($from, $to);
        } elseif ($criterion->getAnniversaryType() == Anniversary::TYPE_REGISTRATION) {
            $customers = $this->customerDetailsRepository->findByCreationAnniversary($from, $to);
        } else {
            return [];
        }

        return array_map(function (CustomerDetails $customerDetails): string {
            return (string) $customerDetails->getCustomerId();
        }, $customers);
    }

    /**
     * @param Criterion $criterion
     *
     * @return bool
     */
    public function support(Criterion $criterion)
    {
        return $criterion instanceof Anniversary;
    }
}
