<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Transformer;

use OpenLoyalty\Bundle\SegmentBundle\Model\Response\Criterion as ReadModelCriterion;
use OpenLoyalty\Bundle\SegmentBundle\Model\Response\Segment as ReadModelSegment;
use OpenLoyalty\Bundle\SegmentBundle\Model\Response\SegmentPart as ReadModelSegmentPart;
use OpenLoyalty\Bundle\SegmentBundle\Provider\CustomerDetailsProviderInterface;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion;
use OpenLoyalty\Component\Segment\Domain\Model\Criterion as DomainCriterion;
use OpenLoyalty\Component\Segment\Domain\Model\SegmentPart as DomainSegmentPart;
use OpenLoyalty\Component\Segment\Domain\Segment as DomainSegment;

/**
 * Class SegmentReadModelTransformer.
 */
class SegmentReadModelTransformer
{
    /**
     * @var CustomerDetailsProviderInterface
     */
    private $customerDetailsProvider;

    /**
     * SegmentReadModelTransformer constructor.
     *
     * @param CustomerDetailsProviderInterface $customerDetailsProvider
     */
    public function __construct(CustomerDetailsProviderInterface $customerDetailsProvider)
    {
        $this->customerDetailsProvider = $customerDetailsProvider;
    }

    /**
     * @param DomainSegment $domainSegment
     *
     * @return ReadModelSegment
     */
    public function transform(DomainSegment $domainSegment): ReadModelSegment
    {
        $segmentParts = [];
        /* @var DomainSegmentPart $segmentPart */
        foreach ($domainSegment->getParts() as $domainSegmentPart) {
            $criteria = [];
            /** @var DomainCriterion $domainCriterion */
            foreach ($domainSegmentPart->getCriteria() as $domainCriterion) {
                $criterionId = (string) $domainCriterion->getCriterionId();
                $type = $domainCriterion->getType();
                $data = $domainCriterion->getDataAsArray();
                if ($type === Criterion::TYPE_CUSTOMER_LIST) {
                    $data['segmentedCustomers'] = $this->extractCustomersDetailsFromCustomerList($data);
                }
                $criteria[] = new ReadModelCriterion($criterionId, $type, $data);
            }
            $segmentParts[] = new ReadModelSegmentPart($domainSegmentPart->getSegmentPartId(), $criteria);
        }

        return new ReadModelSegment(
            (string) $domainSegment->getSegmentId(),
            $domainSegment->getName(),
            $domainSegment->getDescription(),
            $domainSegment->isActive(),
            $segmentParts,
            $domainSegment->getCreatedAt(),
            $domainSegment->getCustomersCount()
        );
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function extractCustomersDetailsFromCustomerList(array $data): array
    {
        return $this->customerDetailsProvider->getCustomers($data['customers']);
    }
}
