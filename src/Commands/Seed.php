<?php

namespace MyCode\Commands;

use Exception;
use MyCode\DB\Models\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Seed extends Command
{
    protected static $defaultName = 'seed';

    protected static $defaultDescription = 'Executes seed for the database.';

    protected function configure(): void
    {
        $this->setHelp(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->seedUsers($input, $io);
        } catch (Exception $e) {
            if (!$input->getOption('quiet')) {
                $io->error('There was an error while running seeder: ' . $e->getMessage());
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function seedUsers(InputInterface $input, SymfonyStyle $io)
    {
        $users = [];

        // @throws Exception
        $users[] = User::create([
            'name' => 'Savio',
            'email' => 'savio@example.com',
            'password' => password_hash('secret', PASSWORD_BCRYPT),
        ]);

        $users[] = User::create([
            'name' => 'Marina',
            'email' => 'marina@example.com',
            'password' => password_hash('secret', PASSWORD_BCRYPT),
        ]);

        if (count($users) !== count(array_filter($users))) {
            throw new Exception('Failed to insert records!');
        }

        if (!$input->getOption('quiet')) {
            $io->success('Records inserted successfully!');
        }
    }
}
