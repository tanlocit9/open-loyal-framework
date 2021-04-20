<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\DependencyInjection\Compiler;

use OpenLoyalty\Bundle\SettingsBundle\Provider\AvailableMarketingVendors;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MarketingConfigCompilerPass.
 */
class MarketingConfigCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $serviceIds = $container->findTaggedServiceIds('ol.settings.marketing.vendors.config');
        $marketingVendorsProvider = $container->findDefinition(AvailableMarketingVendors::class);

        foreach ($serviceIds as $serviceId => $tags) {
            $marketingVendorsProvider->addMethodCall('addVendor', [new Reference($serviceId)]);
        }
    }
}
