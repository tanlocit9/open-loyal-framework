<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\DependencyInjection\Compiler;

use OpenLoyalty\Bundle\SettingsBundle\Provider\ChoicesProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ConfigureChoicesProviderCompilerPass.
 */
class ConfigureChoicesProviderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $serviceIds = $container->findTaggedServiceIds('ol.settings.choices');
        $providerFactory = $container->findDefinition(ChoicesProvider::class);

        foreach ($serviceIds as $serviceId => $tags) {
            $providerFactory->addMethodCall('addChoiceProvider', [new Reference($serviceId)]);
        }
    }
}
