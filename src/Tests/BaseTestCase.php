<?php
namespace Shardman\Symfony\Bundle\Tests;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Shardman\Symfony\Bundle\DependencyInjection\ShardmanExtension;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    public static $container;

    public static function setUpBeforeClass()
    {
        self::$container = self::createContainer();
    }

    public static function getDefaultConfig()
    {
        $file = __DIR__.'/../Resources/config/shard_map.tpl.yml';
        $yamlParser = new Parser();
        return $yamlParser->parse(file_get_contents($file));
    }

    public static function createContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.cache_dir' => __DIR__,
            'kernel.root_dir' => __DIR__.'/Fixtures',
            'kernel.charset' => 'UTF-8',
            'kernel.debug' => true,
            'kernel.bundles' => array('ShardmanBundle' => 'Shardman\\Symfony\\Bundle\\ShardmanBundle'),
        )));

        $extension = new ShardmanExtension();
        $extension->load(self::getDefaultConfig(), $container);
        $container->registerExtension($extension);
        $container->loadFromExtension('shardman', array());
        self::compileContainer($container);

        return $container;
    }

    public static function compileContainer(ContainerBuilder $container)
    {
        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();
    }
}