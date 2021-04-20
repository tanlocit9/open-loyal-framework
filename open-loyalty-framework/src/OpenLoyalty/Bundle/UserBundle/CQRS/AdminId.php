<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\CQRS;

use Assert\AssertionFailedException;
use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use Assert\Assertion as Assert;

/**
 * Class AdminId.
 */
class AdminId implements Identifier
{
    /**
     * @var string
     */
    private $adminId;

    /**
     * AdminId constructor.
     *
     * @param string $adminId
     *
     * @throws AssertionFailedException
     */
    public function __construct(string $adminId)
    {
        Assert::uuid($adminId);

        $this->adminId = $adminId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->adminId;
    }
}
