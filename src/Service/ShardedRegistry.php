<?php


namespace Shardman\Symfony\Bundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Shardman\Service\ShardManager;

/**
 * Class ShardedRegistry.
 * Encapsulates the Doctrine Registry and the ShardManager,
 * providing several convenience methods.
 * @package Shardman\Symfony\Bundle\Service
 */
class ShardedRegistry
{
    /**
     * @var Registry
     */
    private $db;

    /**
     * @var ShardManager
     */
    private $sm;

    public function __construct(Registry $db, ShardManager $sm)
    {
        $this->db = $db;
        $this->sm = $sm;
    }

    /**
     * Returns a repository object for a given shard id
     * @param $entityClass
     * @param $shardId
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($entityClass, $shardId)
    {
        return $this->getManager($shardId)->getRepository($entityClass);
    }

    /**
     * Returns an entity manager for a given shard id
     * @param $shardId
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    public function getManager($shardId)
    {
        return $this->db->getManager($this->getManagerName($shardId));
    }

    protected function getManagerName($shardId)
    {
        return $shardId;
    }

    /**
     * Returns a repository object for a given entity class and sharding key
     * @param $entityClass
     * @param $shardingKey
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepositoryBySk($entityClass, $shardingKey)
    {
        $shardId = $this->sm->getByKey($shardingKey)->getShard()->getId();
        return $this->db->getRepository($entityClass, $shardId);
    }

    /**
     * Returns an entity manager for a given sharding key
     * @param $shardingKey
     * @return EntityManager
     */
    public function getManagerBySk($shardingKey)
    {
        $shardId = $this->sm->getByKey($shardingKey)->getShard()->getId();
        return $this->db->getManager($shardId);
    }

    /**
     * Loads entities from the appropriate shards
     * @param $entityClass
     * @param array $params [shardingKey => id, shardingKey => id]
     * @return array
     */
    public function findByIds($entityClass, array $params)
    {
        $idsByRepository = $this->groupByRepositories($entityClass, $params);
        $result = [];
        foreach ($idsByRepository as $repository) {
            $entities = $repository->findById($idsByRepository[$repository]);
            if ($entities) {
                $result = array_merge($result, $entities);
            }
        }

        return $result;
    }

    /**
     * Returns the values of the $params array grouped by appropriate entity manager.
     * @param array $params [shardingKey => (mixed) data, shardingKey => (mixed) data]
     * @return \SplObjectStorage [
     *                              entityManager  => [(mixed) data, ...],
     *                              entityManager2 => [(mixed) data, ...],
     *                              ...
     *                           ]
     */
    public function groupByManagers(array $params)
    {
        $fun = function ($shardId) {
            return $this->db->getManager($shardId);
        };

        return $this->groupBy($params, $fun);
    }

    /**
     * Groups the values from the $params array by the object returned by $fun
     * @param array $params [shardingKey => (mixed) data, shardingKey => (mixed) data]
     * @param callable $fun
     * @return \SplObjectStorage [
     *                              '$fun_result'  => [(mixed) data, ...],
     *                              '$fun_result2' => [(mixed) data, ...],
     *                              ...
     *                           ]
     */
    protected function groupBy(array $params, \Closure $fun)
    {
        $group = new \SplObjectStorage();
        foreach ($params as $shardingKey => $data) {
            $smResult = $this->sm->getByKey($shardingKey);
            $obj      = $fun($smResult->getShard()->getId());
            if (!isset($group[$obj])) {
                $group[$obj] = [];
            }

            $arr         = $group[$obj];
            $arr[]       = $data;
            $group[$obj] = $arr;
        }

        return $group;
    }

    /**
     * Returns the values of the $params array grouped by appropriate repository object.
     * @param string $entityClass
     * @param array $params [shardingKey => (mixed) data, shardingKey => (mixed) data]
     * @return \SplObjectStorage [
     *                              repository  => [(mixed) data, ...],
     *                              repository2 => [(mixed) data, ...],
     *                              ...
     *                           ]
     */
    public function groupByRepositories($entityClass, array $params)
    {
        $fun = function ($shardId) use ($entityClass) {
            return $this->db->getRepository($entityClass, $shardId);
        };

        return $this->groupBy($params, $fun);
    }

    /**
     * @return Registry
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @return ShardManager
     */
    public function getShardManager()
    {
        return $this->sm;
    }
}