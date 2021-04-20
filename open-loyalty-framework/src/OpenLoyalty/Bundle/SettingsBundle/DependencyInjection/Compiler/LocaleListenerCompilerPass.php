<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\DependencyInjection\Compiler;

use OpenLoyalty\Bundle\SettingsBundle\EventSubscriber\RequestLocaleListener;
use OpenLoyalty\Bundle\SettingsBundle\Service\LocaleProviderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class LocaleListenerCompilerPass.
 */
class LocaleListenerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $localeListener = $container->getDefinition('locale_listener');
        $localeListener->setClass(RequestLocaleListener::class);
        $localeListener->addMethodCall('setLocaleProvider', [new Reference(LocaleProviderInterface::class)]);
    }
}
