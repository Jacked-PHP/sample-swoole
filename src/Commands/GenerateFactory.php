<?php

namespace MyCode\Commands;

use Mustache_Engine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateFactory extends Command
{
    protected static $defaultName = 'generate:factory';

    protected static $defaultDescription = 'Generates a model\'s factory.';

    protected function configure(): void
    {
        $this->setHelp(self::$defaultDescription)
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'The model you are creating this factory for.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $app;

        $io = new SymfonyStyle($input, $output);
        $model = $input->getOption('model');
        $factoryLocation = 'src/DB/Factories/' . $model . 'Factory.php';
        $modelLocation = 'src/DB/Models/' . $model . '.php';
        $stubLocation = 'src/Commands/stubs/ModelFactory.stub';
        $filesystem = $app->getContainer()->get('filesystem');

        if (!$filesystem->fileExists($modelLocation)) {
            $io->error('Model ' . $model . ' (at location ' . $modelLocation . ') doesn\'t exist.');
            return Command::FAILURE;
        }

        if ($filesystem->fileExists($factoryLocation)) {
            $io->error('Factory for model ' . $model . ' (at location ' . $factoryLocation . ') already exist.');
            return Command::FAILURE;
        }

        $content = $filesystem->read($stubLocation);
        $filesystem->write($factoryLocation, $this->processStub($model, $content));

        if (!$filesystem->fileExists($factoryLocation)) {
            $io->error('Command failed to create Factory for model ' . $model . ' (at location ' . $factoryLocation . ').');
            return Command::FAILURE;
        }

        $io->info('Factory created at the location ' . $factoryLocation . '.');
        return Command::SUCCESS;
    }

    private function processStub(string $model, string $content): string
    {
        $mustache = new Mustache_Engine(['entity_flags' => ENT_QUOTES]);

        return $mustache->render($content, ['model' => $model]);
    }
}