<?php

declare(strict_types=1);

namespace BM\BackupManagerBundle\Command;

use BackupManager\Manager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
#[AsCommand(name: 'backup-manager:restore', description: 'Restore from backup.')]
final class RestoreCommand extends Command
{
    public function __construct(private readonly Manager $manager)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument('database', InputArgument::REQUIRED, 'What database configuration do you want to restore to?')
            ->addArgument('destination', InputArgument::REQUIRED, 'What storage do you want to restore from?')
            ->addArgument('file_path', InputArgument::REQUIRED, 'Where on the storage is the file?')
            ->addOption('compression', 'c', InputOption::VALUE_OPTIONAL, 'What file compression is used?', 'null');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $compression = $input->getOption('compression');
        $this->manager->makeRestore()->run(
            (string) $input->getArgument('destination'),
            (string) $input->getArgument('file_path'),
            (string) $input->getArgument('database'),
            is_string($compression) ? $compression : null
        );

        return Command::SUCCESS;
    }
}
