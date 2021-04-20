<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Model\Response;

/**
 * Class SegmentPart.
 */
class SegmentPart
{
    /**
     * @var string
     */
    private $segmentPartId;

    /**
     * @var Criterion[]
     */
    private $criteria;

    /**
     * SegmentPart constructor.
     *
     * @param string      $segmentPartId
     * @param Criterion[] $criteria
     */
    public function __construct(string $segmentPartId, array $criteria)
    {
        $this->segmentPartId = $segmentPartId;
        $this->criteria = $criteria;
    }

    /**
     * @return string
     */
    public function getSegmentPartId(): string
    {
        return $this->segmentPartId;
    }

    /**
     * @return Criterion[]
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }
}
