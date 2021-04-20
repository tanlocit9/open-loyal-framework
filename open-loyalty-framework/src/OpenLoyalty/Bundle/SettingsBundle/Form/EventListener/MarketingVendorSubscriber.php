<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Form\EventListener;

use OpenLoyalty\Bundle\SettingsBundle\Provider\AvailableMarketingVendors;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class MarketingVendorSubscriber.
 */
class MarketingVendorSubscriber implements EventSubscriberInterface
{
    /**
     * @var AvailableMarketingVendors
     */
    private $marketingVendors;

    /**
     * MarketingVendorSubscriber constructor.
     *
     * @param AvailableMarketingVendors $marketingVendors
     */
    public function __construct(AvailableMarketingVendors $marketingVendors)
    {
        $this->marketingVendors = $marketingVendors;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SUBMIT => 'preSetData'];
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data || !array_key_exists('marketingVendorsValue', $data)) {
            return;
        }

        $marketingVendor = $data['marketingVendorsValue'];
        if (!$marketingVendor || $marketingVendor === AvailableMarketingVendors::NONE) {
            return;
        }

        $form = $event->getForm();
        $form->add(
            $marketingVendor,
            $this->marketingVendors->getVendorFormClassName($marketingVendor)
        );
    }
}
