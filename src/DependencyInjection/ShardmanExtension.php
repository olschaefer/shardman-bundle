<?php

namespace Shardman\Symfony\Bundle\DependencyInjection;

use Shardman\Collection\ShardCollection;
use Shardman\Factory\CollectionFactory;
use Shardman\Service\Config\ConfigProvider;
use Shardman\Service\ShardManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ShardmanExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $maps = [];
        foreach ($config['maps'] as $id => $map) {
            foreach ($map['shards'] as $shardId => $shardCfg) {
                $shardCfg['id']          = $shardId;
                $map['shards'][$shardId] = $shardCfg;
            }
            $maps[$id] = $map;
        }

        $namespace = 'shardman';
        $cfgProviderDefinition = new Definition(ConfigProvider::class);
        $cfgProviderDefinition->setArguments([$maps]);
        $cfgProviderId = $namespace.'.config_provider';
        $container->setDefinition($cfgProviderId, $cfgProviderDefinition);
        foreach ($maps as $id => $mapConfig) {
            $selectorClass = $mapConfig['selector']['class'];
            $selectorDefinition = new Definition($selectorClass);
            $selectorId = $namespace.'.'.$id.'.selector';
            $container->setDefinition($selectorId, $selectorDefinition);

            $collectionDefinition = new Definition(ShardCollection::class);
            $collectionDefinition->setFactory([CollectionFactory::class, 'create']);
            $collectionDefinition->setArguments([
                new Expression('service("'.$cfgProviderId.'").getConfig("'.$id.'")')
            ]);
            $collectionId = $namespace.'.'.$id.'.collection';
            $container->setDefinition($collectionId, $collectionDefinition);

            $shardmanId = $namespace.'.'.$id.'.shard_manager';
            $shardManagerDefinition = new Definition(ShardManager::class);
            $shardManagerDefinition->setArguments([
                new Reference($selectorId),
                new Reference($collectionId)
            ]);
            $container->setDefinition($shardmanId, $shardManagerDefinition);
            $container->setAlias(self::getShardManagerServiceAlias($id), $shardmanId);
        }
    }

    public static function getShardManagerServiceAlias($mapId)
    {
        return $mapId.'_shard_manager';
    }
}