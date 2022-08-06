<?php

namespace MyCode\Commands;

use Carbon\Carbon;
use Exception;
use Firebase\JWT\JWT;
use MyCode\DB\Models\Token;
use MyCode\DB\Models\User;
use MyCode\Rules\RecordExist;
use MyCode\Services\JwtToken;
use MyCode\Services\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class GenerateJwtToken extends Command
{
    protected static $defaultName = 'generate:jwt-token';

    protected static $defaultDescription = 'Generates a JWT Token for a user.';

    protected function configure(): void
    {
        $this
            ->setHelp(self::$defaultDescription)
            ->setDefinition(
                new InputDefinition([
                    new InputOption('name', null, InputOption::VALUE_REQUIRED, 'The name of the JWT token.'),
                    new InputOption('user', null, InputOption::VALUE_REQUIRED, 'The email of the user that the JWT Token is for.'),
                    new InputOption('expire', null, InputOption::VALUE_OPTIONAL, 'The number of seconds until the token expiration.'),
                    new InputOption('useLimit', null, InputOption::VALUE_OPTIONAL, 'The number of times this token can be used.'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->generateToken($input, $io);
        } catch (Exception $e) {
            if (!$input->getOption('quiet')) {
                $io->error('There was an error while generating token: ' . $e->getMessage());
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function generateToken(InputInterface $input, SymfonyStyle $io): void
    {
        /** @throws Exception */
        [$name, $userEmail, $expire, $useLimit] = $this->validateInput($input);

        $user = User::where('email', $userEmail)->first();

        $tokenRecord = JwtToken::create(
            name: $name,
            userId: $user->id,
            expire: $expire,
            useLimit: $useLimit
        );

        if (!$input->getOption('quiet')) {
            $io->success('Token successfully generated:' . PHP_EOL . PHP_EOL . $tokenRecord->token);
        }
    }

    /**
     * @param InputInterface $input
     * @return array
     * @throws Exception
     */
    private function validateInput(InputInterface $input): array
    {
        $name = $input->getOption('name');
        $userEmail = $input->getOption('user');
        $expire = $input->getOption('expire');
        $userLimit = $input->getOption('useLimit');

        /** @throws Exception */
        Validator::validate([
            'name' => $name,
            'userEmail' => $userEmail,
            'expire' => $expire,
            'use_limit' => $userLimit,
        ], [
            'name' => [
                new NotBlank(null, 'Token name is requred!'),
                new Type('string', 'Token name must be a string!'),
            ],
            'userEmail' => [
                new NotBlank(null, 'User email is required!'),
                new Type('string', 'User email must be a string!'),
                new Email(null, 'User email must be a valid email!'),
                new RecordExist(
                    [
                        'model' => User::class,
                        'field' => 'email',
                    ],
                    'Email is not registered!'
                ),
            ],
            'expire' => [new Type('integer', 'Expire parameter must be an integer!')],
            'use_limit' => [new Type('integer', 'Use limit parameter must be an integer!')],
        ]);

        return [$name, $userEmail, $expire, $userLimit];
    }
}
