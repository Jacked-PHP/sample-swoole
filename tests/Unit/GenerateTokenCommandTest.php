<?php

namespace Tests\Unit;

use MyCode\DB\Models\User;
use Nekofar\Slim\Test\Traits\AppTestTrait;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\TestCase;

class GenerateTokenCommandTest extends TestCase
{
    use AppTestTrait;

    protected function setUp(): void
    {
        $this->setUpApp($this->getApp());
    }

    private function generateToken(array $args): CommandTester
    {
        return $this->runCommand('generate:jwt-token', $args);
    }

    public function test_can_generate_token_via_command()
    {
        $user = User::find(1);

        $tester = $this->generateToken([
            '--name' => 'token-name',
            '--user' => $user->email,
        ]);

        $tester->assertCommandIsSuccessful();

        $this->assertStringContainsString(
            'Token successfully generated',
            $tester->getDisplay()
        );
    }

    public function test_cant_generate_token_without_name()
    {
        $user = User::find(1);

        $tester = $this->generateToken([
            '--user' => $user->email,
        ]);

        $this->assertStringContainsString(
            'Token name is requred!',
            $tester->getDisplay()
        );
    }

    public function test_cant_generate_token_without_user()
    {
        $tester = $this->generateToken([
            '--name' => 'token-name',
        ]);

        $this->assertStringContainsString(
            'User email is required!',
            $tester->getDisplay()
        );
    }

    public function test_cant_generate_token_with_invalid_email()
    {
        // invalid email type

        $tester = $this->generateToken([
            '--name' => 'token-name',
            '--user' => 1,
        ]);

        $this->assertStringContainsString(
            'User email must be a string!',
            $tester->getDisplay()
        );

        // invalid email format

        $tester = $this->generateToken([
            '--name' => 'token-name',
            '--user' => 'invalidemail',
        ]);

        $this->assertStringContainsString(
            'User email must be a valid email!',
            $tester->getDisplay()
        );

        // not registered email

        $tester = $this->generateToken([
            '--name' => 'token-name',
            '--user' => 'notexistent@email.com',
        ]);

        $this->assertStringContainsString(
            'Email is not registered!',
            $tester->getDisplay()
        );
    }

    public function test_cant_generate_token_with_invalid_expiry_date()
    {
        $user = User::find(1);

        $tester = $this->generateToken([
            '--name' => 'token-name',
            '--user' => $user->email,
            '--expire' => 'savio',
        ]);

        $this->assertStringContainsString(
            'Expire parameter must be an integer!',
            $tester->getDisplay()
        );

        // TODO: make this test also pass when the terminal is small
    }
}