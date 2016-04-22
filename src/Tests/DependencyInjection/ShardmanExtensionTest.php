<?php

namespace Shardman\Symfony\Bundle\Tests\DependencyInjection;

use Shardman\Interfaces\Result;
use Shardman\Service\ShardManager;
use Shardman\Symfony\Bundle\Tests\BaseTestCase;


class ShardmanExtensionTest extends BaseTestCase
{
    public function testNormalOperation()
    {
        $container = self::$container;
        $ids[] = 'shardman.config_provider';
        $ids[] = 'shardman.db.selector';
        $ids[] = 'shardman.db.collection';
        $ids[] = 'shardman.db.shard_manager';
        $ids[] = 'shardman.storage.selector';
        $ids[] = 'shardman.storage.collection';
        $ids[] = 'shardman.storage.shard_manager';
        $definitions = $container->getDefinitions();
        $this->assertSame($ids, array_keys($definitions));
    }
}