<?php

namespace Tests;

use MyCode\DB\Models\User;
use Nekofar\Slim\Test\Traits\AppTestTrait;

class ApiUserTest extends TestCase
{
    use AppTestTrait;

    protected function setUp(): void
    {
        $this->setUpApp($this->getApp());
    }

    public function test_can_get_users(): void
    {
        $user = User::find(1);

        $this->runCommand('jwt-token:generate', [
            '--name' => 'token-name',
            '--user' => $user->email,
            '--quiet' => null,
        ]);

        $user->refresh();
        $token = $user->tokens->first()->token;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/users');
        $response->assertOk();

        $bodyStream = $response->getBody();
        $bodyStream->rewind();
        $data = json_decode($bodyStream->getContents(), true);

        $this->assertArrayHasKey('data', $data);

        $firstRecord = current($data['data']);
        $this->assertArrayHasKey('id', $firstRecord);
        $this->assertArrayHasKey('name', $firstRecord);
        $this->assertArrayHasKey('email', $firstRecord);
    }

    public function test_cant_get_users_without_authorization()
    {
        $response = $this->get('/api/users');
        $response->assertStatus(401);
    }
}