<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Command;

use OpenLoyalty\Component\Account\Domain\AccountId;

/**
 * Class ResetPoints.
 */
class ResetPoints extends AccountCommand
{
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * ResetPoints constructor.
     *
     * @param AccountId $accountId
     * @param \DateTime $date
     */
    public function __construct(AccountId $accountId, \DateTime $date)
    {
        parent::__construct($accountId);
        $this->date = $date;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }
}
