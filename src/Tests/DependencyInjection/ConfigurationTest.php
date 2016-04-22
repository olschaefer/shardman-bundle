<?php
namespace Shardman\Symfony\Bundle\Tests\DependencyInjection;

use Shardman\Service\ShardSelector\Crc32ShardSelector;
use Shardman\Service\ShardSelector\Md5ShardSelector;
use Shardman\Symfony\Bundle\DependencyInjection\Configuration;
use Shardman\Symfony\Bundle\Tests\BaseTestCase;

class ConfigurationTestCase extends BaseTestCase
{
    public function testConfiguration()
    {
        $data = $this->getDefaultConfig();
        $data = $data['shardman'];
        $config = new Configuration();
        $configTree = $config->getConfigTreeBuilder()->buildTree();
        $data = $configTree->normalize($data);
        $finalConfig = $configTree->finalize($data);
        $actual = $this->getDefaultConfig()['shardman'];
        $actual['maps']['db']['selector']['class'] = Crc32ShardSelector::class;
        $actual['maps']['storage']['selector']['class'] = Md5ShardSelector::class;
        $this->assertEquals($finalConfig, $actual);
        $this->assertNull($finalConfig['storage']['shard_selector']);
    }
}