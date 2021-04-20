<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyaltyPlugin\SalesManagoBundle\Event\Listener;

use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerUpdatedSystemEvent;
use OpenLoyaltyPlugin\SalesManagoBundle\Service\SalesManagoContactUpdateSender;
use OpenLoyaltyPlugin\SalesManagoBundle\Service\SalesManagoValidator;

/**
 * Class CustomerSerializationListener.
 */
class CustomerUpdateListener
{
    /**
     * @var SalesManagoContactUpdateSender
     */
    protected $sender;

    /**
     * @var SalesManagoValidator
     */
    protected $validator;

    /**
     * CustomerUpdateListener constructor.
     *
     * @param SalesManagoContactUpdateSender $sender
     * @param SalesManagoValidator           $validator
     */
    public function __construct(SalesManagoContactUpdateSender $sender, SalesManagoValidator $validator)
    {
        $this->sender = $sender;
        $this->validator = $validator;
    }

    /**
     * @param CustomerUpdatedSystemEvent $event
     */
    public function onCustomerUpdated(CustomerUpdatedSystemEvent $event)
    {
        if ($this->validator->verifySalesManagoEnabled()) {
            $this->sender->customerUpdated($event->getCustomerId());
        }
    }
}
