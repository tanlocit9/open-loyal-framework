<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain;

use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use Assert\Assertion as Assert;

/**
 * Class CustomerId.
 */
class CustomerId implements Identifier
{
    /**
     * @var string
     */
    private $customerId;

    /**
     * CustomerId constructor.
     *
     * @param string $customerId
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $customerId)
    {
        Assert::string($customerId);
        Assert::uuid($customerId);

        $this->customerId = $customerId;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->customerId;
    }
}
