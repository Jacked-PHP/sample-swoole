<?php

namespace MyCode\Commands;

use Exception;
use Illuminate\Database\Schema\Blueprint;
use MyCode\DB\Models\User;
use Slim\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Migrate extends Command
{
    protected static $defaultName = 'migrate';

    protected static $defaultDescription = 'Executes migration for the database.';

    protected function configure(): void
    {
        $this
            ->setHelp(self::$defaultDescription)
            ->setDefinition(
                new InputDefinition([
                    new InputOption('fresh', null, InputOption::VALUE_NONE, 'Set the migration to remove existent tables and recreate them.'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->migrateUsers($input, $io);
        } catch (Exception $e) {
            $io->error('There was an error while running migrations: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function migrateUsers(InputInterface $input, SymfonyStyle $io): void
    {
        /** @var App */
        global $app;

        $fresh = $input->getOption('fresh');

        $user = new User;

        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($user->getTable()) && $fresh) {
            $db->drop($user->getTable());
        }

        if (!$db->hasTable($user->getTable()) || $fresh) {
            $db->create($user->getTable(), function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 40);
                $table->string('email', 80)->unique();
                $table->string('password', 150);
                $table->timestamps();
            });

            $io->success('Table created successfully!');
        } else {
            $io->error('Table already exists!');
        }
    }
}
