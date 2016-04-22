<?php
namespace Shardman\Symfony\Bundle\Tests\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Shardman\Interfaces\ShardManager;
use Shardman\Symfony\Bundle\Service\ShardedRegistry;
use Shardman\Symfony\Bundle\Tests\BaseTestCase;

class ShardedRegistryTest extends BaseTestCase
{
    public function getDb()
    {
        $repoMock = $this->getMock(\stdClass::class, ['findById']);
        $repoMock->expects($this->any())
                    ->method('findById')
                    ->willReturnCallback(function() {
                        return [new \stdClass()];
                    });

        $managerMock = $this->getMock(\stdClass::class, ['getRepository']);
        $managerMock->expects($this->any())
                    ->method('getRepository')
                    ->willReturn($repoMock);

        $dbMock = $this->getMock(Registry::class, [], [self::$container, [], [], null, null]);
        $dbMock->expects($this->any())
                ->method('getManager')
                ->willReturn($managerMock);

        $dbMock->expects($this->any())
                ->method('getRepository')
                ->willReturn($repoMock);

        return $dbMock;
    }

    /**
     * @return ShardManager
     */
    public function getShardManager()
    {
        return self::$container->get('db_shard_manager');
    }

    public function testConstructor()
    {
        $sr = new ShardedRegistry($this->getDb(), $this->getShardManager());
        $this->assertInstanceOf(Registry::class, $sr->getDb());
        $this->assertInstanceOf(ShardManager::class, $sr->getShardManager());
    }

    public function testFindByIds()
    {
        $sr = new ShardedRegistry($this->getDb(), $this->getShardManager());
        $result = $sr->findByIds(\stdClass::class, ['testKey' => 'testId']);
        $this->assertEquals(1, count($result));
        $this->assertInstanceOf(\stdClass::class, $result[0]);
    }

    public function testGetRepository()
    {
        $sr = new ShardedRegistry($this->getDb(), $this->getShardManager());
        $this->assertInstanceOf(\stdClass::class, $sr->getRepository(\stdClass::class, 'testShardId'));
        $this->assertInstanceOf(\stdClass::class, $sr->getRepositoryBySk(\stdClass::class, 'testKey'));
    }

    public function testGetManager()
    {
        $sr = new ShardedRegistry($this->getDb(), $this->getShardManager());
        $this->assertInstanceOf(\stdClass::class, $sr->getManager('testShardId'));
        $this->assertInstanceOf(\stdClass::class, $sr->getManagerBySk('testKey'));
    }

    public function testGroupByManagers()
    {
        $testData = 'testData';
        $sr = new ShardedRegistry($this->getDb(), $this->getShardManager());
        $grouped = $sr->groupByManagers([
            'testKey' => $testData,
        ]);

        foreach ($grouped as $manager) {
            $data = $grouped[$manager];
            $this->assertInstanceOf(\stdClass::class, $manager);
            $this->assertSame([$testData], $data);
        }
    }

    public function testGroupByRepositories()
    {
        $testData = 'testData';
        $sr = new ShardedRegistry($this->getDb(), $this->getShardManager());
        $grouped = $sr->groupByRepositories(\stdClass::class, [
            'testKey' => $testData,
        ]);

        foreach ($grouped as $repository) {
            $data = $grouped[$repository];
            $this->assertInstanceOf(\stdClass::class, $repository);
            $this->assertSame([$testData], $data);
        }
    }
}