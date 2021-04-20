<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Command;

use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Model\P2PSpendPointsTransfer;

/**
 * Class TransferPoints.
 */
class TransferPoints extends AccountCommand
{
    /**
     * @var P2PSpendPointsTransfer
     */
    protected $pointsTransfer;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * TransferPoints constructor.
     *
     * @param AccountId              $accountId
     * @param P2PSpendPointsTransfer $pointsTransfer
     * @param \DateTime|null         $createdAt
     */
    public function __construct(AccountId $accountId, P2PSpendPointsTransfer $pointsTransfer, \DateTime $createdAt = null)
    {
        parent::__construct($accountId);
        $this->pointsTransfer = $pointsTransfer;
        $this->createdAt = $createdAt ?: new \DateTime();
    }

    /**
     * @return P2PSpendPointsTransfer
     */
    public function getPointsTransfer(): P2PSpendPointsTransfer
    {
        return $this->pointsTransfer;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
