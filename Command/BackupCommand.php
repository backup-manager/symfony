<?php

namespace BM\BackupManagerBundle\Command;


use BackupManager\Config\Config;
use BackupManager\Filesystems\Destination;
use BackupManager\Filesystems\FilesystemProvider;
use BackupManager\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Backup database.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class BackupCommand extends Command
{
    protected static $defaultName = 'backup-manager:backup';

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var string
     */
    private $filePrefix;

    /**
     *
     * @param Manager $manager
     * @param string $filePrefix
     */
    public function __construct(Manager $manager, $filePrefix)
    {
        $this->manager = $manager;
        $this->filePrefix = $filePrefix;
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Starts a new backup.')
            ->addArgument('database', InputArgument::REQUIRED, 'What database configuration do you want to backup?')
            ->addArgument('destinations', InputArgument::IS_ARRAY, 'What storages do you want to upload the backup to?')
            ->addOption('compression', 'c', InputOption::VALUE_OPTIONAL, 'How do you want to compress the file?', 'null')
            ->addOption('filename', 'name', InputOption::VALUE_OPTIONAL, 'A customized filename', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $filename = $input->getOption('filename')) {
            $filename = $this->filePrefix.(new \DateTime())->format('Y-m-d_H-i-s');
        }

        $destinations = [];
        foreach ($input->getArgument('destinations') as $name) {
            $destinations[] = new Destination($name, $filename);
        }

        $this->manager->makeBackup()->run($input->getArgument('database'), $destinations, $input->getOption('compression'));
    }
}
