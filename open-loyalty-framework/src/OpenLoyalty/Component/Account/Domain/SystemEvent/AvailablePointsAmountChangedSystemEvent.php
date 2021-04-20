<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Domain\SystemEvent;

use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\CustomerId;

/**
 * Class AvailablePointsAmountChangedSystemEvent.
 */
class AvailablePointsAmountChangedSystemEvent extends AccountSystemEvent
{
    const OPERATION_TYPE_ADD = 'add';
    const OPERATION_TYPE_P2P_ADD = 'p2p_add';
    const OPERATION_TYPE_SUBTRACT = 'subtract';
    const OPERATION_TYPE_P2P_SUBTRACT = 'p2p_subtract';

    /**
     * @var float
     */
    protected $currentAmount;

    /**
     * @var CustomerId
     */
    protected $customerId;

    /**
     * @var int
     */
    protected $amountChange;

    /**
     * @var string
     */
    protected $operationType;

    /**
     * AvailablePointsAmountChangedSystemEvent constructor.
     *
     * @param AccountId  $accountId
     * @param CustomerId $customerId
     * @param float      $currentAmount
     * @param int        $amountChange
     * @param string     $operationType
     */
    public function __construct(AccountId $accountId, CustomerId $customerId, $currentAmount, $amountChange = 0, $operationType = self::OPERATION_TYPE_SUBTRACT)
    {
        parent::__construct($accountId);
        $this->customerId = $customerId;
        $this->currentAmount = $currentAmount;
        $this->amountChange = $amountChange;
        $this->operationType = $operationType;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId(): ?CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return float
     */
    public function getCurrentAmount(): ?float
    {
        return $this->currentAmount;
    }

    /**
     * @return int
     */
    public function getAmountChange(): ?int
    {
        return $this->amountChange;
    }

    /**
     * @return string
     */
    public function getOperationType(): ?string
    {
        return $this->operationType;
    }
}
