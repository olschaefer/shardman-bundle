<?php

namespace Shardman\Symfony\Bundle\DependencyInjection;

use Shardman\Service\ShardSelector\Md5ShardSelector;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('shardman');

        $rootNode
            ->children()
                ->arrayNode('maps')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('selector')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('class')->defaultValue(Md5ShardSelector::class)->end()
                                ->end()
                            ->end()
                            ->arrayNode('shards')
                                ->useAttributeAsKey('id')
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('bucketRanges')
                                            ->prototype('array')
                                                ->children()
                                                    ->integerNode('start')->end()
                                                    ->integerNode('end')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
