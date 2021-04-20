<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Event;

use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\CustomerId;

/**
 * Class AccountWasCreated.
 */
class AccountWasCreated extends AccountEvent
{
    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * AccountWasCreated constructor.
     *
     * @param AccountId  $accountId
     * @param CustomerId $customerId
     */
    public function __construct(AccountId $accountId, CustomerId $customerId)
    {
        parent::__construct($accountId);
        $this->customerId = $customerId;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    public function serialize(): array
    {
        return array_merge(
            parent::serialize(),
            [
                'customerId' => $this->customerId->__toString(),
            ]
        );
    }

    /**
     * @param array $data
     *
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new self(new AccountId($data['accountId']), new CustomerId($data['customerId']));
    }
}
