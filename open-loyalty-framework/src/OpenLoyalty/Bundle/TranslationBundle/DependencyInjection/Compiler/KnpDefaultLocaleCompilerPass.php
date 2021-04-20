<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TranslationBundle\DependencyInjection\Compiler;

use OpenLoyalty\Bundle\SettingsBundle\Service\LocaleProvider;
use OpenLoyalty\Bundle\TranslationBundle\Provider\DefaultLocaleCallable;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class KnpDefaultLocaleCompilerPass.
 */
class KnpDefaultLocaleCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('knp.doctrine_behaviors.translatable_subscriber.default_locale_callable');
        $definition->setClass(DefaultLocaleCallable::class);
        $definition->setLazy(true);
        $definition->addMethodCall('setLocaleProvider', [new Reference(LocaleProvider::class)]);
    }
}
