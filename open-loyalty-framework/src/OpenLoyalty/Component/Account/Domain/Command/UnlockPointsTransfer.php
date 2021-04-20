<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Command;

use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;

/**
 * Class UnlockPointsTransfer.
 */
class UnlockPointsTransfer extends AccountCommand
{
    /**
     * @var PointsTransferId
     */
    protected $pointsTransferId;

    /**
     * UnlockPointsTransfer constructor.
     *
     * @param AccountId        $accountId
     * @param PointsTransferId $pointsTransferId
     */
    public function __construct(AccountId $accountId, PointsTransferId $pointsTransferId)
    {
        parent::__construct($accountId);
        $this->pointsTransferId = $pointsTransferId;
    }

    /**
     * @return PointsTransferId
     */
    public function getPointsTransferId()
    {
        return $this->pointsTransferId;
    }
}
