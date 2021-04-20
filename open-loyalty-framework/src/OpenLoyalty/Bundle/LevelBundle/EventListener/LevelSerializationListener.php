<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\LevelBundle\EventListener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\GenericSerializationVisitor;
use OpenLoyalty\Component\Level\Domain\ReadModel\LevelDetails;
use PhpOption\None;

/**
 * Class LevelSerializationListener.
 */
class LevelSerializationListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ['event' => 'serializer.post_serialize', 'method' => 'onPostSerialize'],
        ];
    }

    /**
     * On serializer.post_serialize, choose a translated name to be presented
     * to the client based on the request's locale.
     *
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event): void
    {
        /** @var LevelDetails $level */
        $level = $event->getObject();

        if ($level instanceof LevelDetails) {
            /** @var GenericSerializationVisitor $visitor */
            $visitor = $event->getVisitor();
            $context = $event->getContext();

            $localeOption = $context->attributes->get('locale');
            if ($localeOption && !$localeOption instanceof None) {
                $locale = $context->attributes->get('locale')->get();

                if ($visitor->hasData('translations')) {
                    $translations = $level->getTranslations();
                    if (isset($translations[$locale]['name'])) {
                        $visitor->setData('name', $translations[$locale]['name']);
                    }
                }
            }
        }
    }
}
