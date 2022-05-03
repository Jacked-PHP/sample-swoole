<?php

namespace MyCode\DB;

use Illuminate\Database\Schema\Blueprint;
use MyCode\DB\Models\User;
use Slim\App;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

class Migration
{
    public static function handle(App $app, InputInterface $input)
    {
        self::migrateUsers($app, $input->getOption('fresh'));
    }

    private static function migrateUsers(App $app, bool $fresh): void
    {
        $output = new ConsoleOutput;

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

            $output->writeln('Table created successfully!');
        } else {
            $output->writeln('<error>Table already exists!</error>');
        }
    }
}