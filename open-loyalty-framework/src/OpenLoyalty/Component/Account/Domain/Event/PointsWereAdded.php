<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\Event;

use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;

/**
 * Class PointsWereAdded.
 */
class PointsWereAdded extends AccountEvent
{
    /**
     * @var AddPointsTransfer
     */
    protected $pointsTransfer;

    public function __construct(AccountId $accountId, AddPointsTransfer $pointsTransfer)
    {
        parent::__construct($accountId);
        $this->pointsTransfer = $pointsTransfer;
    }

    /**
     * @return AddPointsTransfer
     */
    public function getPointsTransfer()
    {
        return $this->pointsTransfer;
    }

    public function serialize(): array
    {
        return array_merge(
            parent::serialize(),
            [
                'pointsTransfer' => $this->pointsTransfer->serialize(),
            ]
        );
    }

    public static function deserialize(array $data)
    {
        return new self(new AccountId($data['accountId']), AddPointsTransfer::deserialize($data['pointsTransfer']));
    }
}
