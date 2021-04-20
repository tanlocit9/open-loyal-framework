<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

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
    protected $customerId;

    /**
     * CustomerId constructor.
     *
     * @param string $customerId
     */
    public function __construct($customerId)
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

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }
}
