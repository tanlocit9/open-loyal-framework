<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('open_loyalty_settings');
        $rootNode
            ->children()
            ->arrayNode('mapping')
                ->useAttributeAsKey('image')
                ->prototype('array')
                ->beforeNormalization()
                ->ifString()
                ->then(function ($v) {
                    return array('type' => $v);
                })
                ->end()
                ->treatNullLike(array())
                ->treatFalseLike(array('mapping' => false))
                ->performNoDeepMerging()
                    ->children()
                        ->arrayNode('sizes')
                        ->arrayPrototype()
                        ->children()
                            ->scalarNode('width')->end()
                            ->scalarNode('height')->end()
                        ->end()
                ->end()
            ->end();

        $rootNode->children()
                ->arrayNode('locales_map')
                ->useAttributeAsKey('translation')
                ->arrayPrototype()
                ->children()
                ->scalarNode('translation')->end()
                ->scalarNode('locale')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
