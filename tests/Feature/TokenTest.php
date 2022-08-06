<?php

namespace Tests\Feature;

use Faker\Factory;
use MyCode\DB\Models\User;
use Tests\TestCase;
use Tests\Traits\SwooleAppTestTrait;

class TokenTest extends TestCase
{
    use SwooleAppTestTrait;

    protected function setUp(): void
    {
        $this->setUpApp($this->getApp());

        $this->faker = Factory::create();
    }

    public function test_can_generate_token()
    {
        $user = User::find(1);

        $this->runCommand('generate:jwt-token', [
            '--name' => 'token-name',
            '--user' => $user->email,
            '--quiet' => null,
        ]);

        $user->refresh();
        $token = $user->tokens->first()->token;

        $response1 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/users');
        $response1->assertOk();

        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/users');
        $response2->assertOk();
    }

    public function test_can_generate_token_with_limit()
    {
        $user = User::find(1);

        $this->runCommand('generate:jwt-token', [
            '--name' => 'token-name',
            '--user' => $user->email,
            '--useLimit' => 1,
            '--quiet' => null,
        ]);

        $user->refresh();
        $token = $user->tokens->first()->token;

        $response1 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/users');
        $response1->assertOk();

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get('/api/users');
        $response2->assertStatus(401);
    }
}