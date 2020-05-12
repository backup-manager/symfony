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
 * Restore from backup.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class RestoreCommand extends Command
{
    protected static $defaultName = 'backup-manager:restore';

    /**
     * @var Manager
     */
    private $manager;

    /**
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Restore form backup.')
            ->addArgument('database', InputArgument::REQUIRED, 'What database configuration do you want to backup?')
            ->addArgument('destination', InputArgument::REQUIRED, 'What storage do you want to restore from?')
            ->addArgument('file_path', InputArgument::REQUIRED, 'Where on the storage is the file?')
            ->addOption('compression', 'c', InputOption::VALUE_OPTIONAL, 'What file compression is used?', 'null')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->manager->makeRestore()->run(
            $input->getArgument('destination'),
            $input->getArgument('file_path'),
            $input->getArgument('database'),
            $input->getOption('compression')
        );
        
        return 0;
    }

}
