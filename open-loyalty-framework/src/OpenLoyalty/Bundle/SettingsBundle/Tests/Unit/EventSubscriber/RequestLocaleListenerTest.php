<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Tests\Unit\EventSubscriber;

use OpenLoyalty\Bundle\SettingsBundle\EventSubscriber\RequestLocaleListener;
use OpenLoyalty\Bundle\SettingsBundle\Service\LocaleProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class RequestLocaleListenerTest.
 */
class RequestLocaleListenerTest extends TestCase
{
    const CONFIG_DEFAULT_LOCALE = 'en';

    /**
     * @param Request $request
     * @param string  $defaultLocale
     *
     * @return RequestLocaleListener
     */
    protected function createRequestListener(Request $request, string $defaultLocale): RequestLocaleListener
    {
        $localeProvider = $this->getMockForAbstractClass(LocaleProviderInterface::class);
        $localeProvider->expects($this->any())->method('getConfigurationDefaultLocale')->willReturn($defaultLocale);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $requestListener = new RequestLocaleListener(
            $requestStack,
            self::CONFIG_DEFAULT_LOCALE
        );
        $requestListener->setLocaleProvider($localeProvider);

        return $requestListener;
    }

    /**
     * @test
     */
    public function it_sets_default_locale_from_configuration(): void
    {
        $request = new Request();
        $listener = $this->createRequestListener($request, 'de');
        $httpKernelInterface = $this->getMockForAbstractClass(HttpKernelInterface::class);
        $event = new GetResponseEvent($httpKernelInterface, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener->onKernelRequest($event);
        $this->assertEquals('de', $event->getRequest()->getLocale());
        $this->assertEquals('de', $event->getRequest()->getDefaultLocale());
    }

    /**
     * @test
     */
    public function it_sets_locale_from_request(): void
    {
        $request = new Request(['_locale' => 'de']);
        $listener = $this->createRequestListener($request, 'pl');
        $httpKernelInterface = $this->getMockForAbstractClass(HttpKernelInterface::class);
        $event = new GetResponseEvent($httpKernelInterface, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener->onKernelRequest($event);
        $this->assertEquals('de', $event->getRequest()->getLocale());
        $this->assertEquals('pl', $event->getRequest()->getDefaultLocale());
    }
}
