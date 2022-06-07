<?php

namespace MyCode\Commands;

use Carbon\Carbon;
use Exception;
use Firebase\JWT\JWT;
use MyCode\DB\Models\Token;
use MyCode\DB\Models\User;
use MyCode\Services\JwtToken;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateJwtToken extends Command
{
    protected static $defaultName = 'jwt-token:generate';

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
        $name = $input->getOption('name');
        $userEmail = $input->getOption('user');
        $expire = $input->getOption('expire');

        // TODO validate inputs

        $user = User::where('email', $userEmail)->first();
        if (null !== $expire) {
            $expire = Carbon::now()->addSeconds($expire);
        }

        $payload = [
            "iat" => Carbon::now()->timestamp,
            "user_id" => $user->id,
        ];

        if (null !== $expire) {
            $payload["exp"] = $expire->timestamp;
        }

        $token = JWT::encode($payload, $name, JwtToken::HS256_ALGORITHM);

        $tokenRecord = Token::create([
            'name' => $name,
            'user_id' => $user->id,
            'expire_at' => $expire === null ? null : $expire->format('Y-m-d H:i:s'),
            'token' => $token,
        ]);

        if (null === $tokenRecord) {
            throw new Exception('Couldn\'t save token!');
        }

        if (!$input->getOption('quiet')) {
            $io->success('Token successfully generated:' . PHP_EOL . PHP_EOL . $token);
        }
    }
}
