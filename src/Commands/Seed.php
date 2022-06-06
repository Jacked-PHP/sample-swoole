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
            $this->seedUsers($io);
        } catch (Exception $e) {
            $io->error('There was an error while running seeder: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function seedUsers(SymfonyStyle $io)
    {
        // @throws Exception
        $user = User::create([
            'name' => 'Savio',
            'email' => 'savio@example.com',
            'password' => password_hash('secret', PASSWORD_BCRYPT),
        ]);

        if (null === $user) {
            throw new Exception('Failed to insert record!');
        }

        $io->success('Records inserted successfully!');
    }
}
