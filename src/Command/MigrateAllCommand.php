<?php


namespace Shardman\Symfony\Bundle\Command;

use Shardman\Interfaces\ShardManager;
use Shardman\Symfony\Bundle\DependencyInjection\ShardmanExtension;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;

/**
 * Class MigrateAllCommand.
 * Performs migration of all shards.
 * @package Shardman\Symfony\Bundle\Command
 */
class MigrateAllCommand  extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('shardman:migrateall')
            ->addOption('map', null, InputOption::VALUE_OPTIONAL, 'Sharding map to migrate', 'db')
            ->addArgument('version')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $map = $input->getOption('map');
        /** @var ShardManager $sm */
        $sm  = $this->container->get(ShardmanExtension::getShardManagerServiceAlias($map));
        $ver = (string) $input->getArgument('version');
        foreach ($sm->getShardIds() as $shardId) {
            $output->writeln('Starting migration for '.$shardId);
            $process = new Process($_SERVER['argv'][0].' -n doctrine:migrations:migrate --em='.$shardId.' '.$ver);
            $process->mustRun(function ($type, $buffer) use ($output) {
                $output->write($buffer);
            });
        }
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}