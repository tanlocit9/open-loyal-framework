<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Event;

use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Model\P2PSpendPointsTransfer;

/**
 * Class PointsWereTransferred.
 */
class PointsWereTransferred extends AccountEvent
{
    /**
     * @var P2PSpendPointsTransfer
     */
    protected $pointsTransfer;

    /**
     * PointsWereTransferred constructor.
     *
     * @param AccountId              $accountId
     * @param P2PSpendPointsTransfer $pointsTransfer
     */
    public function __construct(AccountId $accountId, P2PSpendPointsTransfer $pointsTransfer)
    {
        parent::__construct($accountId);
        $this->pointsTransfer = $pointsTransfer;
    }

    /**
     * @return P2PSpendPointsTransfer
     */
    public function getPointsTransfer(): P2PSpendPointsTransfer
    {
        return $this->pointsTransfer;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return array_merge(
            parent::serialize(),
            [
                'pointsTransfer' => $this->pointsTransfer->serialize(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new self(new AccountId($data['accountId']), P2PSpendPointsTransfer::deserialize($data['pointsTransfer']));
    }
}
