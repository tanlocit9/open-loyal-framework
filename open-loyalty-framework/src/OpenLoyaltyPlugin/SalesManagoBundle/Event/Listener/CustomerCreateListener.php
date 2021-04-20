<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyaltyPlugin\SalesManagoBundle\Event\Listener;

use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerRegisteredSystemEvent;
use OpenLoyaltyPlugin\SalesManagoBundle\Service\SalesManagoContactUpdateSender;
use OpenLoyaltyPlugin\SalesManagoBundle\Service\SalesManagoValidator;

/**
 * Class CustomerSerializationListener.
 */
class CustomerCreateListener
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
     * CustomerCreateListener constructor.
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
     * @param CustomerRegisteredSystemEvent $event
     */
    public function onCustomerCreated(CustomerRegisteredSystemEvent $event)
    {
        if ($this->validator->verifySalesManagoEnabled()) {
            $this->sender->customerCreated($event->getCustomerId());
        }
    }
}
