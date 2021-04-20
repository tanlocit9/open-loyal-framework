<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyaltyPlugin\SalesManagoBundle\Event\Listener;

use OpenLoyalty\Component\Segment\Domain\SystemEvent\CustomerAddedToSegmentSystemEvent;
use OpenLoyalty\Component\Segment\Domain\SystemEvent\CustomerRemovedFromSegmentSystemEvent;
use OpenLoyaltyPlugin\SalesManagoBundle\Service\SalesManagoContactSegmentTagsSender;
use OpenLoyaltyPlugin\SalesManagoBundle\Service\SalesManagoValidator;

/**
 * Class CustomerSerializationListener.
 */
class CustomerSegmentListener
{
    /**
     * @var SalesManagoContactSegmentTagsSender
     */
    protected $sender;

    /**
     * @var SalesManagoValidator
     */
    protected $validator;

    /**
     * CustomerSegmentListener constructor.
     *
     * @param SalesManagoContactSegmentTagsSender $sender
     * @param SalesManagoValidator                $validator
     */
    public function __construct(SalesManagoContactSegmentTagsSender $sender, SalesManagoValidator $validator)
    {
        $this->sender = $sender;
        $this->validator = $validator;
    }

    /**
     * @param CustomerAddedToSegmentSystemEvent $event
     */
    public function onCustomerAddedToSegment(CustomerAddedToSegmentSystemEvent $event)
    {
        if ($this->validator->verifySalesManagoEnabled()) {
            $this->sender->customerSegmentAdd($event);
        }
    }

    /**
     * @param CustomerRemovedFromSegmentSystemEvent $event
     */
    public function onCustomerRemovedFromSegment(CustomerRemovedFromSegmentSystemEvent $event)
    {
        if ($this->validator->verifySalesManagoEnabled()) {
            $this->sender->customerSegmentRemove($event);
        }
    }
}
