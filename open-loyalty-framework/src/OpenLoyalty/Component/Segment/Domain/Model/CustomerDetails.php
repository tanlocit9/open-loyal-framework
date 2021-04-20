<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Model;

/**
 * Class CustomerDetails.
 */
class CustomerDetails
{
    /**
     * @var string
     */
    private $customerId;

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var string|null
     */
    private $phone;

    public function __construct(string $customerId, ?string $email, ?string $telephone)
    {
        $this->customerId = $customerId;
        $this->email = $email;
        $this->phone = $telephone;
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }
}
