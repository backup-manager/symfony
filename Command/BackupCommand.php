<?php

namespace BM\BackupManagerBundle\Command;


use BackupManager\Config\Config;
use BackupManager\Filesystems\Destination;
use BackupManager\Filesystems\FilesystemProvider;
use BackupManager\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BackupCommand extends Command
{
    protected static $defaultName = 'backup-manager:backup';

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var Config
     */
    private $storageConfig;

    /**
     * @var FilesystemProvider
     */
    private $filesystemProvider;

    /**
     *
     * @param Manager $manager
     * @param Config $config
     * @param FilesystemProvider $fp
     */
    public function __construct(Manager $manager, Config $config, FilesystemProvider $fp)
    {
        $this->manager = $manager;
        $this->storageConfig = $config;
        $this->filesystemProvider = $fp;
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('backup-manager:backup')
            ->setDescription('Starts a new backup.')
            ->addArgument('database', InputArgument::REQUIRED, 'What database configuration do you want to backup?')
            ->addArgument('destinations', InputArgument::IS_ARRAY, 'What storages do you want to upload the backup to?')
            ->addArgument('compression', InputArgument::OPTIONAL, 'How do you want to compress the file?', 'gzip')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $destinations = $this->getDestinationsFromArray($input->getArgument('destinations'));

        $this->manager->makeBackup()->run($input->getArgument('database'), $destinations, $input->getArgument('compression'));
    }

    /**
     * Get Destination classes.
     *
     * @param array $input
     * @return array
     * @throws \BackupManager\Config\ConfigFieldNotFound
     * @throws \BackupManager\Config\ConfigNotFoundForConnection
     * @throws \BackupManager\Filesystems\FilesystemTypeNotSupported
     */
    private function getDestinationsFromArray(array $input)
    {
        $destinations = [];

        foreach ($input as $name) {
            $config = $this->storageConfig->get($name);
            $path = isset($config['root']) ? $config['root'] : $config['container'];
            $destinations[] = new Destination($this->filesystemProvider->get($name), $path);
        }

        return $destinations;
    }
}