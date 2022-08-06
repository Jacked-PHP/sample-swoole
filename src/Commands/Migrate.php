<?php

namespace MyCode\Commands;

use Exception;
use Illuminate\Database\Schema\Blueprint;
use MyCode\DB\Models\Token;
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
            $this->migrateJwt($input, $io);
        } catch (Exception $e) {
            if (!$input->getOption('quiet')) {
                $io->error('There was an error while running migrations: ' . $e->getMessage());
            }
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

            if (!$input->getOption('quiet')) {
                $io->success('Table created successfully!');
            }
        } else {
            if (!$input->getOption('quiet')) {
                $io->error('Table already exists!');
            }
        }
    }

    private function migrateJwt(InputInterface $input, SymfonyStyle $io): void
    {
        /** @var App */
        global $app;

        $fresh = $input->getOption('fresh');

        $token = new Token;

        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($token->getTable()) && $fresh) {
            $db->drop($token->getTable());
        }

        if (!$db->hasTable($token->getTable()) || $fresh) {
            $db->create($token->getTable(), function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 40);
                $table->foreignId('user_id');
                $table->dateTime('expire_at')->nullable();
                $table->string('token', 150);
                $table->integer('uses')->default(0);
                $table->integer('use_limit')->default(0);
                $table->timestamps();
            });

            if (!$input->getOption('quiet')) {
                $io->success('Tokens table created successfully!');
            }
        } else {
            if (!$input->getOption('quiet')) {
                $io->error('Tokens table already exists!');
            }
        }
    }
}
