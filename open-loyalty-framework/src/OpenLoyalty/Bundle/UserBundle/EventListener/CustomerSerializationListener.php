<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\EventListener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\GenericSerializationVisitor;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\LevelRepository;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosId;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use PhpOption\None;

/**
 * Class CustomerSerializationListener.
 */
class CustomerSerializationListener implements EventSubscriberInterface
{
    /**
     * @var LevelRepository
     */
    protected $levelRepository;

    /**
     * @var SettingsManager
     */
    protected $settingsManager;

    /**
     * @var PosRepository
     */
    protected $posRepository;

    /**
     * CustomerSerializationListener constructor.
     *
     * @param LevelRepository $levelRepository
     * @param SettingsManager $settingsManager
     * @param PosRepository   $posRepository
     */
    public function __construct(
        LevelRepository $levelRepository,
        SettingsManager $settingsManager,
        PosRepository $posRepository
    ) {
        $this->levelRepository = $levelRepository;
        $this->settingsManager = $settingsManager;
        $this->posRepository = $posRepository;
    }

    public static function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.post_serialize', 'method' => 'onPostSerialize'),
        );
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        /** @var CustomerDetails $customer */
        $customer = $event->getObject();

        if ($customer instanceof CustomerDetails) {
            /** @var GenericSerializationVisitor $visitor */
            $visitor = $event->getVisitor();
            $context = $event->getContext();

            $currency = $this->settingsManager->getSettingByKey('currency');
            $currency = $currency ? $currency->getValue() : 'PLN';
            $visitor->setData('currency', $currency);

            $customerSegmentsOption = $context->attributes->get('customerSegments');
            if ($customerSegmentsOption && !$customerSegmentsOption instanceof None) {
                $segments = $context->attributes->get('customerSegments')->get();
                $visitor->setData('segments', $segments);
            }

            if ($customer->getLevelId()) {
                $level = $this->levelRepository->byId(new LevelId($customer->getLevelId()->__toString()));
                if ($level instanceof Level && $level->getReward()) {
                    $visitor->setData('levelPercent', number_format($level->getReward()->getValue() * 100, 2).'%');
                }
            }

            if ($customer->getPosId()) {
                $pos = $this->posRepository->byId(new PosId($customer->getPosId()->__toString()));
                if ($pos instanceof Pos) {
                    $visitor->setData('posIdentifier', $pos->getIdentifier());
                }
            }
        }
    }
}
