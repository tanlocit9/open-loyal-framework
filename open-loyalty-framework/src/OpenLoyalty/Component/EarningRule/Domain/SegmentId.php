<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use Assert\AssertionFailedException;
use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use Assert\Assertion as Assert;

/**
 * Class SegmentId.
 */
class SegmentId implements Identifier
{
    /**
     * @var string
     */
    protected $segmentId;

    /**
     * SegmentId constructor.
     *
     * @param $segmentId
     *
     * @throws AssertionFailedException
     */
    public function __construct($segmentId)
    {
        Assert::string($segmentId);
        Assert::uuid($segmentId);
        $this->segmentId = $segmentId;
    }

    public function __toString()
    {
        return $this->segmentId;
    }
}
