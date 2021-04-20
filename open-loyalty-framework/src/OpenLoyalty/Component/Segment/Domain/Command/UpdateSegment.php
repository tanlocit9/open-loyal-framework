<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Command;

use OpenLoyalty\Component\Segment\Domain\Model\Criterion;
use OpenLoyalty\Component\Segment\Domain\Model\SegmentPart;
use OpenLoyalty\Component\Segment\Domain\SegmentId;
use Assert\Assertion as Assert;

/**
 * Class UpdateSegment.
 */
class UpdateSegment extends SegmentCommand
{
    /**
     * @var array
     */
    protected $segmentData;

    public function __construct(SegmentId $segmentId, array $segmentData)
    {
        $this->validate($segmentData);
        parent::__construct($segmentId);
        $this->segmentData = $segmentData;
    }

    protected function validate(array $segmentData)
    {
        if (isset($segmentData['parts'])) {
            Assert::greaterOrEqualThan(count($segmentData['parts']), 1);
            foreach ($segmentData['parts'] as $part) {
                SegmentPart::validate($part);
                foreach ($part['criteria'] as $criterion) {
                    $map = Criterion::TYPE_MAP;
                    if (!isset($map[$criterion['type']])) {
                        throw new \Exception('type '.$criterion['type'].' does not exists');
                    }
                    $map[$criterion['type']]::validate($criterion);
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getSegmentData()
    {
        return $this->segmentData;
    }
}
