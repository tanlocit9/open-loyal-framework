<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use Assert\AssertionFailedException;
use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use Assert\Assertion as Assert;

/**
 * Class PosId.
 */
class PosId implements Identifier
{
    /**
     * @var string
     */
    protected $posId;

    /**
     * PosId constructor.
     *
     * @param string $posId
     *
     * @throws AssertionFailedException
     */
    public function __construct(string $posId)
    {
        Assert::string($posId);
        Assert::uuid($posId);
        $this->posId = $posId;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->posId;
    }
}
