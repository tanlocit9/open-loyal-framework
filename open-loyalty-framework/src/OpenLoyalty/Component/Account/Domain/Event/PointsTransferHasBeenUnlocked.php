<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Event;

use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;

/**
 * Class PointsTransferHasBeenUnlocked.
 */
class PointsTransferHasBeenUnlocked extends AccountEvent
{
    /**
     * @var PointsTransferId
     */
    protected $pointsTransferId;

    /**
     * PointsTransferHasBeenUnlocked constructor.
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

    /**
     * @return array
     */
    public function serialize(): array
    {
        return array_merge(
            parent::serialize(),
            [
                'pointsTransferId' => $this->pointsTransferId->__toString(),
            ]
        );
    }

    /**
     * @param array $data
     *
     * @return PointsTransferHasBeenUnlocked
     */
    public static function deserialize(array $data)
    {
        return new self(new AccountId($data['accountId']), new PointsTransferId($data['pointsTransferId']));
    }
}
