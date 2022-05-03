<?php

namespace MyCode\DB;

use Exception;
use MyCode\DB\Models\User;
use Slim\App;
use Symfony\Component\Console\Output\ConsoleOutput;

class Seed
{
    public static function handle(App $app)
    {
        self::seedUsers();
    }

    private static function seedUsers()
    {
        $output = new ConsoleOutput;

        try {
            $user = User::create([
                'name' => 'Savio',
                'email' => 'savio@example.com',
                'password' => password_hash('secret', PASSWORD_BCRYPT),
            ]);
        } catch(Exception $e) {
            $output->writeln('<error>Failed to insert record with the following error: ' . $e->getMessage() . '!</error>');
            return;
        }

        if (null === $user) {
            $output->writeln('<error>Failed to insert record!</error>');
            return;
        }

        $output->writeln('Records inserted successfully!');
    }
}