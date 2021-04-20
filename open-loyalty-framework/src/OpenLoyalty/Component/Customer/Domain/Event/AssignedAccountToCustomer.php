<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Domain\Event;

use OpenLoyalty\Component\Customer\Domain\AccountId;
use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class AssignedAccountToCustomer.
 */
class AssignedAccountToCustomer extends CustomerEvent
{
    /**
     * @var AccountId
     */
    private $accountId;

    /**
     * {@inheritdoc}
     */
    public function __construct(CustomerId $customerId, AccountId $accountId)
    {
        parent::__construct($customerId);

        $this->accountId = $accountId;
    }

    /**
     * @return AccountId
     */
    public function getAccountId(): AccountId
    {
        return $this->accountId;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return array_merge(
            parent::serialize(),
            ['accountId' => (string) $this->accountId]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new self(
            new CustomerId($data['customerId']),
            new AccountId($data['accountId'])
        );
    }
}
