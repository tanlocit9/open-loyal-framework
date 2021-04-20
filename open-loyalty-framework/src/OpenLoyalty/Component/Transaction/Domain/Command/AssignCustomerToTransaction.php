<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\Command;

use OpenLoyalty\Component\Transaction\Domain\CustomerId;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class AssignCustomerToTransaction.
 */
class AssignCustomerToTransaction extends TransactionCommand
{
    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var string|null
     */
    protected $email;

    /**
     * @var string|null
     */
    protected $phone;

    /**
     * {@inheritdoc}
     *
     * @param CustomerId  $customerId
     * @param string|null $email
     * @param string|null $phone
     */
    public function __construct(
        TransactionId $transactionId,
        CustomerId $customerId,
        string $email = null,
        string $phone = null
    ) {
        parent::__construct($transactionId);

        $this->customerId = $customerId;
        $this->email = $email;
        $this->phone = $phone;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
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
