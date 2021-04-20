<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\EventSubscriber;

use OpenLoyalty\Bundle\SettingsBundle\Service\LocaleProviderInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\EventListener\LocaleListener;

/**
 * Class RequestLocaleListener.
 */
class RequestLocaleListener extends LocaleListener
{
    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * {@inheritdoc}
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        parent::onKernelRequest($event);

        $request = $event->getRequest();
        $request->setDefaultLocale($this->localeProvider->getConfigurationDefaultLocale());

        if ($locale = $request->get('_locale')) {
            $request->setLocale($locale);
        }
    }

    /**
     * @return LocaleProviderInterface
     */
    public function getLocaleProvider(): LocaleProviderInterface
    {
        return $this->localeProvider;
    }

    /**
     * @param LocaleProviderInterface $localeProvider
     */
    public function setLocaleProvider(LocaleProviderInterface $localeProvider): void
    {
        $this->localeProvider = $localeProvider;
    }
}
