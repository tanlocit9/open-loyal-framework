<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain;

use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use Assert\Assertion as Assert;

/**
 * Class PointsTransferId.
 */
class PointsTransferId implements Identifier
{
    /**
     * @var string
     */
    protected $pointsTransferId;

    /**
     * PointsTransferId constructor.
     *
     * @param string $pointsTransferId
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $pointsTransferId)
    {
        Assert::uuid($pointsTransferId);

        $this->pointsTransferId = $pointsTransferId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->pointsTransferId;
    }
}
