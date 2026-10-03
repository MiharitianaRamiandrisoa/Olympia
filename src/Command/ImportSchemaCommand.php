<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:database:import-schema',
    description: 'Crée les tables Olympia depuis Schema/schema.sql.'
)]
final class ImportSchemaCommand extends Command
{
    public function __construct(private readonly Connection $connection, private readonly KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $schemaPath = $this->kernel->getProjectDir().'/../../Schema/schema.sql';
        if (!is_file($schemaPath)) {
            $output->writeln('<error>Schema/schema.sql est introuvable.</error>');
            return Command::FAILURE;
        }

        $sql = file_get_contents($schemaPath);
        if ($sql === false) {
            $output->writeln('<error>Impossible de lire le fichier SQL.</error>');
            return Command::FAILURE;
        }

        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
        $statements = preg_split('/;\s*(?:\R|$)/', $sql) ?: [];
        $executed = 0;
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '' || preg_match('/^(--|CREATE DATABASE|USE )/i', $statement)) {
                continue;
            }

            $this->connection->executeStatement($statement);
            ++$executed;
        }

        $output->writeln(sprintf('<info>%d instructions SQL exécutées.</info>', $executed));
        return Command::SUCCESS;
    }
}
