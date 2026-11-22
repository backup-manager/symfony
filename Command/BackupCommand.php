<?php

declare(strict_types=1);

namespace BM\BackupManagerBundle\Command;

use BackupManager\Filesystems\Destination;
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
#[AsCommand(name: 'backup-manager:backup', description: 'Starts a new backup.')]
final class BackupCommand extends Command
{
    public function __construct(
        private readonly Manager $manager,
        private readonly string $filePrefix = '',
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument('database', InputArgument::REQUIRED, 'What database configuration do you want to backup?')
            ->addArgument('destinations', InputArgument::IS_ARRAY, 'What storages do you want to upload the backup to? Must be array.')
            ->addOption('compression', 'c', InputOption::VALUE_OPTIONAL, 'How do you want to compress the file?', 'null')
            ->addOption('filename', null, InputOption::VALUE_OPTIONAL, 'A customized filename', null);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getOption('filename');
        if (!is_string($filename) || $filename === '') {
            $filename = $this->filePrefix . (new \DateTimeImmutable())->format('Y-m-d_H-i-s');
        }

        $destinations = [];
        foreach ($input->getArgument('destinations') as $name) {
            $destinations[] = new Destination($name, $filename);
        }

        $this->manager->makeBackup()->run($input->getArgument('database'), $destinations, self::compression($input));

        return Command::SUCCESS;
    }

    /**
     * "-c" without a value means no compression.
     */
    private static function compression(InputInterface $input): string
    {
        $compression = $input->getOption('compression');

        return is_string($compression) && $compression !== '' ? $compression : 'null';
    }
}
