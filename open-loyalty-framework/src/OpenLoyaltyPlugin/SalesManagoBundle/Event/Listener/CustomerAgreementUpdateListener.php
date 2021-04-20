<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyaltyPlugin\SalesManagoBundle\Event\Listener;

use OpenLoyalty\Component\Customer\Domain\SystemEvent\CustomerAgreementsUpdatedSystemEvent;
use OpenLoyaltyPlugin\SalesManagoBundle\Service\SalesManagoContactSegmentTagsSender;
use OpenLoyaltyPlugin\SalesManagoBundle\Service\SalesManagoValidator;

/**
 * Class CustomerSerializationListener.
 */
class CustomerAgreementUpdateListener
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
     * CustomerCreateListener constructor.
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
     * @param CustomerAgreementsUpdatedSystemEvent $event
     */
    public function onCustomerAgreementUpdate(CustomerAgreementsUpdatedSystemEvent $event)
    {
        if ($this->validator->verifySalesManagoEnabled()) {
            $this->sender->customerAgreementChanged($event);
        }
    }
}
