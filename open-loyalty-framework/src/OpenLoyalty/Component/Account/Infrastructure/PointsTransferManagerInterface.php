<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Infrastructure;

use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\PointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\TransactionId;

interface PointsTransferManagerInterface
{
    const DEFAULT_VALIDITY_IN_DAYS = 30;

    /**
     * @param PointsTransferId   $id
     * @param                    $value
     * @param \DateTime|null     $createdAt
     * @param bool               $canceled
     * @param TransactionId|null $transactionId
     * @param null|string        $comment
     * @param string             $issuer
     *
     * @return AddPointsTransfer
     */
    public function createAddPointsTransferInstance(
        PointsTransferId $id,
        $value,
        \DateTime $createdAt = null,
        bool $canceled = false,
        TransactionId $transactionId = null,
        ?string $comment = null,
        $issuer = PointsTransfer::ISSUER_SYSTEM
    ): AddPointsTransfer;
}
