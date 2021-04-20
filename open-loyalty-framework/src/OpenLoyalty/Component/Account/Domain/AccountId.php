<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain;

use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use Assert\Assertion as Assert;

/**
 * Class AccountId.
 */
class AccountId implements Identifier
{
    /**
     * @var string
     */
    private $accountId;

    /**
     * AccountId constructor.
     *
     * @param string $accountId
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $accountId)
    {
        Assert::uuid($accountId);

        $this->accountId = $accountId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->accountId;
    }
}
