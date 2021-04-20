<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyaltyPlugin\SalesManagoBundle\Event\Listener;

use OpenLoyalty\Component\Transaction\Domain\SystemEvent\CustomerAssignedToTransactionSystemEvent;
use OpenLoyaltyPlugin\SalesManagoBundle\Service\SalesManagoContactTransactionSender;
use OpenLoyaltyPlugin\SalesManagoBundle\Service\SalesManagoValidator;

/**
 * Class CustomerSerializationListener.
 */
class CustomerTransactionListener
{
    /**
     * @var SalesManagoContactTransactionSender
     */
    protected $sender;

    /**
     * @var SalesManagoValidator
     */
    protected $validator;

    /**
     * CustomerCreateListener constructor.
     *
     * @param SalesManagoContactTransactionSender $sender
     * @param SalesManagoValidator                $validator
     */
    public function __construct(SalesManagoContactTransactionSender $sender, SalesManagoValidator $validator)
    {
        $this->sender = $sender;
        $this->validator = $validator;
    }

    /**
     * @param CustomerAssignedToTransactionSystemEvent $event
     */
    public function onCustomerTransactionRegistered($event)
    {
        if ($this->validator->verifySalesManagoEnabled()) {
            $this->sender->customerTransactionRegistered($event);
        }
    }
}
