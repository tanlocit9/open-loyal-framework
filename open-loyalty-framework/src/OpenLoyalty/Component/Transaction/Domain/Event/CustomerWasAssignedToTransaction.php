<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\Event;

use OpenLoyalty\Component\Transaction\Domain\CustomerId;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class CustomerWasAssignedToTransaction.
 */
class CustomerWasAssignedToTransaction extends TransactionEvent
{
    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var null|string
     */
    protected $email;

    /**
     * @var null|string
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
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return array_merge(parent::serialize(), [
            'customerId' => (string) $this->customerId,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @return CustomerWasAssignedToTransaction
     */
    public static function deserialize(array $data)
    {
        return new self(
            new TransactionId($data['transactionId']),
            new CustomerId($data['customerId']),
            $data['email'],
            $data['phone']
        );
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return null|string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }
}
