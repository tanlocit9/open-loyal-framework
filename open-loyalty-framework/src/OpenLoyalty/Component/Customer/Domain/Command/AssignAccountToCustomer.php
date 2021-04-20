<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Domain\Command;

use OpenLoyalty\Component\Customer\Domain\AccountId;
use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class AssignAccountToCustomer.
 */
class AssignAccountToCustomer extends CustomerCommand
{
    /**
     * @var AccountId
     */
    private $accountId;

    /**
     * AssignAccountToCustomer constructor.
     *
     * @param CustomerId $customerId
     * @param AccountId  $accountId
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
}
